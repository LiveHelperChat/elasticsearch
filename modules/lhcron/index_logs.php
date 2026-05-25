<?php

// /usr/bin/php cron.php -s site_admin -e elasticsearch -c cron/index_logs -p <limit_days>,<max_per_run>

erLhcoreClassModelChatConfig::$disableCache = true;

$esOptions = erLhcoreClassModelChatConfig::fetch('elasticsearch_options');
$dataOptions = (array)$esOptions->data;

if (isset($dataOptions['disable_es']) && $dataOptions['disable_es'] == 1) {
    echo "Elastic Search is disabled!\n";
    exit;
}

$cronParams = explode(',', $cronjobPathOption->value);
$days = isset($cronParams[0]) && is_numeric($cronParams[0]) ? (int)$cronParams[0] : 0;
$maxIterations = isset($cronParams[1]) && is_numeric($cronParams[1]) ? (int)$cronParams[1] : 0;

$pageLimit = 500;


$lastMessageId = $dataOptions['last_index_log_msg_id'] ?? 0;
$totalIndex = 0;
$batchIndex = 0;

// Upper bound: last_msg_id of the most recent chat (by id) older than $days days.
$boundChat = erLhcoreClassModelChat::findOne(array(
    'filterlt'  => array('time' => time() - ($days * 24 * 3600)),
    'filternot' => array('last_msg_id' => 0),
    'sort'      => 'id DESC',
));
$maxMsgId = $boundChat ? $boundChat->last_msg_id : false;

echo "Upper bound: ",$maxMsgId,"\n";

if ($maxMsgId === false) {
    echo "No chats found for the given day range.\n";
    exit;
}

while (true) {

    $batchIndex++;
    echo "Saving msg - ", $batchIndex, "\n";

    if ($maxIterations > 0 && $batchIndex > $maxIterations) {
        echo "Max iterations limit reached: ", $maxIterations, "\n";
        break;
    }

    $messages = erLhcoreClassModelmsg::getList(array(
        'filtergt'  => array('id' => $lastMessageId),
        'filterlte' => array('id' => $maxMsgId),
        'limit'     => $pageLimit,
        'sort'      => 'id ASC'
    ));
    $messagesFound = count($messages);
    $lastIndexTemporary = 0;

    $cutoffOld = time() - 30 * 24 * 3600;

    foreach ($messages as $keyItem => $item) {
        $lastIndexTemporary = $item->id;
        if ($item->meta_msg == '' || 
            !str_contains($item->meta_msg, '"debug":true') || 
            str_contains($item->meta_msg,'"ex":"es_log"') ) { // Skip non-debug messages or ones already transferred
                unset($messages[$keyItem]);
                continue;
        }
        if ($item->time < $cutoffOld) { // Message is older than 30 days, alter meta_msg and skip indexing
            $item->meta_msg = '{"content":{"html":{"ex":"es_log","debug":true,"content":""}}}';
            $item->updateThis(['update' => ['meta_msg']]);
            unset($messages[$keyItem]);
            continue;
        }
    }

    // No messages were found
    if (empty($messagesFound)) {
        break;
    }

    // No messages to index were found
    if (empty($messages)) {
        $lastMessageId = $lastIndexTemporary;
        if ($messagesFound < $pageLimit) {
            break;
        }
        continue;
    }

    $batchHasError = false;
    $indexedIds = [];

    $response = erLhcoreClassElasticSearchIndex::indexLogMessages(array('messages' => $messages));
    foreach (($response['new'] ?? []) as $indexItem) {
        if (isset($indexItem['errors']) && $indexItem['errors'] > 0) {
            foreach ($indexItem['items'] as $item) {
                if (isset($item['index']['error'])) {
                    echo 'Message index error - ' . json_encode($item['index']['error']) . "\n";
                    error_log('Message index error - ' . json_encode($item['index']['error']));
                    $batchHasError = true;
                } else {
                    $indexedIds[] = $item['index']['_id'];
                }
            }
        } else {
            foreach ($indexItem['items'] as $item) {
                $indexedIds[] = $item['index']['_id'];
            }
        }
    }

    if ((count($indexedIds) + count($response['old'])) != count($messages)) {
        echo 'Indexed and retrieved message count differs: Expected [' . count($messages) . "] vs [" . count($indexedIds) . "]\n";
        error_log('Indexed and retrieved message count differs: Expected [' . count($messages) . "] vs [" . count($indexedIds) . "]");
        $batchHasError = true;
    } else {
        echo 'Indexed and retrieved message count: Old [' . count($response['old']) . "] New [" . count($indexedIds) . "]\n";
        foreach ($indexedIds as $indexedMessage) {
            if (isset($messages[$indexedMessage])) {
                $messages[$indexedMessage]->meta_msg = '{"content":{"html":{"ex":"es_log","debug":true,"content":""}}}';
                $messages[$indexedMessage]->updateThis(['update' => ['meta_msg']]);
            }
        }
    }

    // It will retry next time
    if ($batchHasError === true) {
        break;
    }

    $totalIndex += count($messages);

    $lastMessageId = $lastIndexTemporary;
}

$esOptions = erLhcoreClassModelChatConfig::fetch('elasticsearch_options');
$data = (array)$esOptions->data;
$data['last_index_log_msg_id'] = $lastMessageId;

$esOptions->value = serialize($data);
$esOptions->saveThis();

echo "-=STARTING OLDER RECORDS THAN 30 DAYS Deletion=-\n";

$cutoffTime = (time() - 30 * 24 * 3600) * 1000; // 30 days in milliseconds (ES stores time * 1000)

$deleteSparamsEs = array('body' => array());
$deleteSparamsEs['body']['query']['bool']['must'][]['range']['time']['lt'] = $cutoffTime;

$deleteOffset = 0;
$deleteLimit = 100;

\LiveHelperChatExtension\elasticsearch\providers\Index\RestLog::getSession();

do {
    $oldItems = \LiveHelperChatExtension\elasticsearch\providers\Index\RestLog::getList(array(
        'body'   => $deleteSparamsEs['body'],
        'offset' => $deleteOffset,
        'limit'  => $deleteLimit,
    ));

    $found = count($oldItems);
    echo "Deleting ES log records: ", $found, "\n";

    if ($found > 0) {
        \LiveHelperChatExtension\elasticsearch\providers\Index\RestLog::bulkDelete($oldItems, ['static_index' => true]);
    }

} while ($found === $deleteLimit);

echo "-=DONE=-\n";