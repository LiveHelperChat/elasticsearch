<?php

// /usr/bin/php cron.php -s site_admin -e elasticsearch -c cron/index_os

echo "Indexing online sessions\n";

$pageLimit = 500;

$parts = ceil(erLhcoreClassModelUserOnlineSession::getCount()/$pageLimit);

for ($i = 0; $i < $parts; $i++) {

    echo "Saving online session page - ",($i + 1),"\n";
    
    $items = erLhcoreClassModelUserOnlineSession::getList(array('offset' => $i*$pageLimit, 'limit' => $pageLimit, 'sort' => 'id ASC'));
    
    erLhcoreClassElasticSearchIndex::indexOs(array('items' => $items));
}