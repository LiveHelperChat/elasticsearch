<?php

// Run every week
// 22 8 4 * * cd /home/www/lhc && php cron.php -s site_admin -e elasticsearch -c cron/remove_duplicates > log_duplicates.txt /dev/null 2>&1
// php cron.php -s site_admin -e elasticsearch -c cron/remove_duplicates

$elasticSearchHandler = erLhcoreClassElasticClient::getHandler();

$dateFilter = array('filtergte'=> array('time' => time() - 30*24*3600));

$indexSearch = erLhcoreClassElasticSearchStatistic::getIndexByFilter($dateFilter, erLhcoreClassModelESChat::$elasticType);

$sparams = array();
$sparams['index'] = $indexSearch;
$sparams['body']['query']['bool']['must'][]['range']['time']['gt'] = mktime(0, 0, 0, date('m'), date('d') - 7, date('y')) * 1000;
$sparams['body']['size'] = 0;
$sparams['body']['from'] = 0;
$sparams['body']['aggs']['group_by_chat']['terms']['field'] = 'chat_id';
$sparams['body']['aggs']['group_by_chat']['terms']['size'] = 10000;
$sparams['body']['aggs']['group_by_chat']['terms']['min_doc_count'] = 2;

$sparams['ignore_unavailable'] = true;

$response = $elasticSearchHandler->search($sparams);

foreach ($response['aggregations']['group_by_chat']['buckets'] as $bucket) {
    if ($bucket['doc_count'] > 1) {

        $sparams = array();
        $sparams['query']['bool']['must'][]['term']['chat_id'] = (int)trim($bucket['key']);

        $chats = erLhcoreClassModelESChat::getList(array(
            'offset' => 0,
            'limit' => 10,
            'body' => $sparams
        ),
        array('date_index' => array('gte' => time() - 30*24*3600)));

        $allRemoved = true;
        $counter = 0;
        foreach ($chats as $chat) {
            if ($counter > 0 || !is_numeric($chat->id)) {
                $chat->removeThisOnly();
                echo $chat->chat_id , '-', $chat->id,"\n";
            } else {
                echo "Skipping\n";
                $allRemoved = false;
            }

            $counter++;
        }

        // If all chats were removed because id was string reindex chat
        if ($allRemoved == true) {
            $chat = new erLhcoreClassModelChat();
            $chat->id = (int)trim($bucket['key']);
            erLhcoreClassElasticSearchIndex::indexChatDelay(['chat' => $chat]);
        }
    }
}

?>