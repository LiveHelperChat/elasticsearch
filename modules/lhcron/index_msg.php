<?php

// /usr/bin/php cron.php -s site_admin -e elasticsearch -c cron/index_msg

$esOptions = erLhcoreClassModelChatConfig::fetch('elasticsearch_options');
$dataOptions = (array)$esOptions->data;

if (isset($dataOptions['disable_es']) && $dataOptions['disable_es'] == 1) {
    echo "Elastic Search is disabled!\n";
    exit;
}

echo "Indexing chats\n";

$pageLimit = 500;
$lastId = 0;

for ($i = 0; $i < 100000; $i++) {

    echo "Saving msg - ",($i + 1),"\n";

    $messages = erLhcoreClassModelmsg::getList(array('offset' => 0, 'filtergt' => array('id' => $lastId), 'limit' => $pageLimit, 'sort' => 'id ASC'));
    end($messages);
    $lastMessage = current($messages);

    if (!is_object($lastMessage)) {
        exit;
    }

    $lastId = $lastMessage->id;

    echo $lastId,'-',count($messages),"\n";

    if (empty($messages)){
        exit;
    }

    erLhcoreClassElasticSearchIndex::indexMessages(array('messages' => $messages));
}

?>