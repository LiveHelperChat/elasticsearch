<?php

// /usr/bin/php cron.php -s site_admin -e elasticsearch -c cron/index_msg

echo "Indexing chats\n";

$pageLimit = 500;
$lastId = 0;

for ($i = 0; $i < 100000; $i++) {

    echo "Saving msg - ",($i + 1),"\n";

    $messages = erLhcoreClassModelmsg::getList(array('offset' => 0, 'filtergt' => array('id' => $lastId), 'limit' => $pageLimit, 'sort' => 'id ASC'));
    end($messages);
    $lastMessage = current($messages);

    $lastId = $lastMessage->id;

    echo $lastId,'-',count($messages),"\n";

    if (empty($messages)){
        exit;
    }

    erLhcoreClassElasticSearchIndex::indexMessages(array('messages' => $messages));
}

?>