<?php

// /usr/bin/php cron.php -s site_admin -e elasticsearch -c cron/index_chats -p <last_chat_id>

$esOptions = erLhcoreClassModelChatConfig::fetch('elasticsearch_options');
$dataOptions = (array)$esOptions->data;

if (isset($dataOptions['disable_es']) && $dataOptions['disable_es'] == 1) {
    echo "Elastic Search is disabled!\n";
    exit;
}

if (is_numeric($cronjobPathOption->value)) {
    $lastId = (int)$cronjobPathOption->value;
} else {
    $lastId = 0;
}

echo "Indexing chats\n";

$pageLimit = 500;

for ($i = 0; $i < 1000000; $i++) {

    echo "Saving chats - ",($i + 1),"\n";

    $chats = erLhcoreClassModelChat::getList(array('offset' => 0, 'filtergt' => array('id' => $lastId), 'limit' => $pageLimit, 'sort' => 'id ASC'));

    if (!empty($chats))
    {
        end($chats);
        $lastChat = current($chats);

        $lastId = $lastChat->id;

        echo $lastId,'-',count($chats),"\n";

        if (empty($chats)){
            exit;
        }

        erLhcoreClassElasticSearchIndex::indexChats(array('chats' => $chats));
    } else {
        echo "No chats to index!\n";
        exit;
    }
}

?>
