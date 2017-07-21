<?php

// /usr/bin/php cron.php -s site_admin -e elasticsearch -c cron/index_chats

echo "Indexing chats\n";

$pageLimit = 100;

$parts = ceil(erLhcoreClassModelChat::getCount()/$pageLimit);

for ($i = 0; $i < $parts; $i++) {

    echo "Saving chat page - ",($i + 1),"\n";

    $items = erLhcoreClassModelChat::getList(array('offset' => $i*$pageLimit, 'limit' => $pageLimit, 'sort' => 'id ASC'));
        
    erLhcoreClassElasticSearchIndex::indexChats(array('chats' => $items));
}

?>