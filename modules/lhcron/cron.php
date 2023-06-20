<?php
// Run me every 5 minutes
// /usr/bin/php cron.php -s site_admin -e elasticsearch -c cron/cron

echo "==Indexing messages== \n";

$esOptions = erLhcoreClassModelChatConfig::fetch('elasticsearch_options');
$data = (array)$esOptions->data;

if (!isset($data['last_index_msg_id'])) {
    echo "Please set last message id in back office\n";
    exit;
}

if (!isset($data['last_index_part_id'])) {
    echo "Please set last participant id in back office\n";
    exit;
}

if (isset($data['disable_es']) && $data['disable_es'] == 1) {
    echo "Elastic Search is disabled!\n";
    exit;
}

erLhcoreClassElasticSearchIndex::$ts = time();

echo "\n==Indexing participants == \n";

$pageLimit = 500;

$parts = ceil(erLhcoreClassChat::getCount(array('filtergt' => array('id' => $data['last_index_part_id'])),'lh_chat_participant')/$pageLimit);

$lastMessageId = $data['last_index_part_id'];
$totalIndex = 0;

for ($i = 0; $i < $parts; $i++) {

    echo "Saving participant - ",($i + 1),"\n";
    $messages = \LiveHelperChat\Models\LHCAbstract\ChatParticipant::getList(array('filtergt' => array('id' => $data['last_index_part_id']), 'offset' => $i*$pageLimit, 'limit' => $pageLimit, 'sort' => 'id ASC'));
    erLhcoreClassElasticSearchIndex::indexParticipant(array('participant' => $messages));

    $totalIndex += count($messages);

    if (!empty($messages)) {
        end($messages);
        $lastMsg = current($messages);

        $lastMessageId = $lastMsg->id;
    }
}

$data['last_index_part_id'] = $lastMessageId;

echo "Last participant id - ",$data['last_index_part_id'],", total indexed - {$totalIndex}\n";

$esOptions->value = serialize($data);
$esOptions->saveThis();

echo "\n==Indexing messages == \n";

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

for ($i = 0; $i < 100; $i++) {

    $db->beginTransaction();
    $stmt = $db->prepare('SELECT chat_id FROM lhc_lheschat_index WHERE status = 0 LIMIT :limit FOR UPDATE ');
    $stmt->bindValue(':limit',100,PDO::PARAM_INT);
    $stmt->execute();
    $chatsId = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (!empty($chatsId)) {

        // Update records as being pending indexing
        $stmt = $db->prepare('UPDATE lhc_lheschat_index SET status = 1 WHERE chat_id IN (' . implode(',', $chatsId) . ')');
        $stmt->execute();
        $db->commit();

        $chats = erLhcoreClassModelChat::getList(array('filterin' => array('id' => $chatsId)));

        try {
            if (!empty($chats)){
                $totalIndex+= count($chats);
                erLhcoreClassElasticSearchIndex::indexChats(array('chats' => $chats));
            }
        } catch (Exception $e) {
            echo $e->getMessage(),"\n";
            error_log($e->getMessage() . "\n" . $e->getTraceAsString());
        }

        // Delete chats if all ok
        $stmt = $db->prepare('DELETE FROM lhc_lheschat_index WHERE chat_id IN (' . implode(',', $chatsId) . ')');
        $stmt->execute();

    } else {
        $db->rollback();
        break;
    }
}

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

echo "\n==Reindexing failed chats==\n";

$stmt = $db->prepare('SELECT chat_id FROM lhc_lheschat_index WHERE status = 1 LIMIT :limit');
$stmt->bindValue(':limit',100,PDO::PARAM_INT);
$stmt->execute();
$chatsId = $stmt->fetchAll(PDO::FETCH_COLUMN);

$totalIndex = 0;

if (!empty($chatsId)) {

    $chats = erLhcoreClassModelChat::getList(array('filterin' => array('id' => $chatsId)));

    try {

        if (!empty($chats)){
            $totalIndex+= count($chats);
            erLhcoreClassElasticSearchIndex::indexChats(array('chats' => $chats));
        }

        // Delete chats if all ok
        $stmt = $db->prepare('DELETE FROM lhc_lheschat_index WHERE chat_id IN (' . implode(',', $chatsId) . ')');
        $stmt->execute();

    } catch (Exception $e) {
        echo $e->getMessage(),"\n";
        error_log($e->getMessage() . "\n" . $e->getTraceAsString());
    }
}

echo "total re-indexed chats - {$totalIndex}\n";

// Just even that we are indexing something
// So extensions can index their own things
\erLhcoreClassChatEventDispatcher::getInstance()->dispatch('system.elastic_search.index_objects',array());

?>