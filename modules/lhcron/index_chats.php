<?php

// /usr/bin/php cron.php -s site_admin -e elasticsearch -c cron/index_chats

echo "Indexing chats\n";

$pageLimit = 500;

$lastId = 0;

for ($i = 0; $i < 1000000; $i++) {

    echo "Saving msg - ",($i + 1),"\n";

    $chats = erLhcoreClassModelChat::getList(array('offset' => 0, 'filtergt' => array('id' => $lastId), 'limit' => $pageLimit, 'sort' => 'id ASC'));
    end($chats);
    $lastChat = current($chats);

    $lastId = $lastChat->id;

    echo $lastId,'-',count($chats),"\n";

    if (empty($chats)){
        exit;
    }

    erLhcoreClassElasticSearchIndex::indexChats(array('chats' => $chats));
}

?>
