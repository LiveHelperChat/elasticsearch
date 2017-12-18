<?php

// /usr/bin/php56 cron.php -s site_admin -e elasticsearch -c cron/index_chats -p <last_chat_id>

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
