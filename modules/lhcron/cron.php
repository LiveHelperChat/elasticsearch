<?php
// Run me every 5 minutes
// /usr/bin/php cron.php -s site_admin -e elasticsearch -c cron/cron

echo "==Indexing messages== \n";

erLhcoreClassElasticSearchIndex::$ts = time();

$esOptions = erLhcoreClassModelChatConfig::fetch('elasticsearch_options');
$data = (array)$esOptions->data;

if (!isset($data['last_index_msg_id'])) {
    echo "Please set last message id in back office\n";
    exit;
}

$pageLimit = 500;

$parts = ceil(erLhcoreClassChat::getCount(array('filtergt' => array('id' => $data['last_index_msg_id'])),'lh_msg')/$pageLimit);

$lastMessageId = $data['last_index_msg_id'];
$totalIndex = 0;

for ($i = 0; $i < $parts; $i++) {

    echo "Saving msg - ",($i + 1),"\n";
    $messages = erLhcoreClassModelmsg::getList(array('filtergt' => array('id' => $data['last_index_msg_id']), 'offset' => $i*$pageLimit, 'limit' => $pageLimit, 'sort' => 'id ASC'));
    erLhcoreClassElasticSearchIndex::indexMessages(array('messages' => $messages)); 
       
    $totalIndex += count($messages);
    
    if (!empty($messages)) {
        end($messages);
        $lastMsg = current($messages);
        
        $lastMessageId = $lastMsg->id;
    }
}

$data['last_index_msg_id'] = $lastMessageId;

echo "Last message id - ",$data['last_index_msg_id'],", total indexed - {$totalIndex}\n";

$esOptions->value = serialize($data);
$esOptions->saveThis();

echo "\n==Indexing chats== \n";

$totalIndex = 0;

$db = ezcDbInstance::get();
$db->beginTransaction();

for ($i = 0; $i < 100; $i++) {
    $stmt = $db->prepare('SELECT chat_id FROM lhc_lheschat_index LIMIT :limit FOR UPDATE ');
    $stmt->bindValue(':limit',100,PDO::PARAM_INT);
    $stmt->execute();
    $chatsId = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (!empty($chatsId)) {
        $chats = erLhcoreClassModelChat::getList(array('filterin' => array('id' => $chatsId)));
        
        if (!empty($chats)){
            $totalIndex+= count($chats);
            erLhcoreClassElasticSearchIndex::indexChats(array('chats' => $chats));
        }
        
        // Delete indexed chat's records
        $stmt = $db->prepare('DELETE FROM lhc_lheschat_index WHERE chat_id IN (' . implode(',', $chatsId) . ')');
        $stmt->execute();

    } else {
        break;
    }
}

$db->commit();

echo "total indexed chats - {$totalIndex}\n";

echo "\n==Indexing online sessions== \n";

$pageLimit = 500;

$totalIndex = 0;

$tsFilter = erLhcoreClassElasticSearchIndex::$ts - 10*60;

$parts = ceil(erLhcoreClassModelUserOnlineSession::getCount(array('filtergt' => array('lactivity' => $tsFilter)))/$pageLimit);

for ($i = 0; $i < $parts; $i++) {

    echo "Saving Online Session Page - ",($i + 1),"\n";    
    $items = erLhcoreClassModelUserOnlineSession::getList(array('filtergt' => array('lactivity' => $tsFilter), 'offset' => $i*$pageLimit, 'limit' => $pageLimit, 'sort' => 'id ASC'));

    $totalIndex += count($items);

    erLhcoreClassElasticSearchIndex::indexOs(array('items' => $items));
}

echo "total indexed OS - {$totalIndex}\n";

?>