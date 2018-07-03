<?php
// Run me every minute
// /usr/bin/php cron.php -s site_admin -e elasticsearch -c cron/cron_1m

$esOptions = erLhcoreClassModelChatConfig::fetch('elasticsearch_options');
$data = (array)$esOptions->data;

if (isset($data['last_index_date_pending']) && $data['last_index_date_pending'] == date('YmdHi')) {
    echo "This minute was already indexed!\n";
    exit;
}

if (isset($data['disable_es']) && $data['disable_es'] == 1) {
    echo "Elastic Search is disabled!\n";
    exit;
}

erLhcoreClassElasticSearchIndex::$ts = time();

echo "==Indexing pending chats== \n";

$data['last_index_date_pending'] = date('YmdHi');
$esOptions->value = serialize($data);
$esOptions->saveThis();

$totalIndex = 0;

$pageLimit = 100;

$parts = ceil(erLhcoreClassModelChat::getCount(array('use_index' => 'status', 'filter' => array('status' => erLhcoreClassModelChat::STATUS_PENDING_CHAT)))/$pageLimit);

for ($i = 0; $i < $parts; $i++) {

    echo "Pending chats records - ",($i + 1),"\n";
    $items = erLhcoreClassModelChat::getList(array('use_index' => 'status', 'filter' => array('status' => erLhcoreClassModelChat::STATUS_PENDING_CHAT), 'offset' => $i*$pageLimit, 'limit' => $pageLimit, 'sort' => 'id ASC'));

    erLhcoreClassElasticSearchIndex::indexPendingChats(array('items' => $items));
    
    $totalIndex += count($items);  
}

echo "Total indexed - ",$totalIndex,"\n";

echo "==Indexing active chats== \n";

$totalIndex = 0;

$pageLimit = 100;

$parts = ceil(erLhcoreClassModelChat::getCount(array('use_index' => 'status', 'filter' => array('status' => erLhcoreClassModelChat::STATUS_ACTIVE_CHAT)))/$pageLimit);

for ($i = 0; $i < $parts; $i++) {

    echo "Pending chats records",($i + 1),"\n";
    $items = erLhcoreClassModelChat::getList(array('use_index' => 'status', 'filter' => array('status' => erLhcoreClassModelChat::STATUS_ACTIVE_CHAT), 'offset' => $i*$pageLimit, 'limit' => $pageLimit, 'sort' => 'id ASC'));

    erLhcoreClassElasticSearchIndex::indexPendingChats(array('items' => $items));

    $totalIndex += count($items);
}

echo "Total indexed - ",$totalIndex,"\n";

/*
 *  @todo I do not have chat close time to determine it's close time
 *  
echo "==Indexing abandon chats== \n";

$totalIndex = 0;

$pageLimit = 100;

$tsFilter = time()-60;

$parts = ceil(erLhcoreClassModelChat::getCount(array('filtergt' => array('time' => $tsFilter),'filter' => array('user_id' => 0, 'status' => erLhcoreClassModelChat::STATUS_CLOSED_CHAT)))/$pageLimit);

for ($i = 0; $i < $parts; $i++) {

    echo "Pending chats records",($i + 1),"\n";
    $items = erLhcoreClassModelChat::getList(array('filter' => array('status' => erLhcoreClassModelChat::STATUS_CLOSED_CHAT), 'offset' => $i*$pageLimit, 'limit' => $pageLimit, 'sort' => 'id ASC'));

    erLhcoreClassElasticSearchIndex::indexPendingChats(array('items' => $items));

    $totalIndex += count($items);
}*/

erLhcoreClassElasticSearchIndex::indexOnlineOperators();

?>