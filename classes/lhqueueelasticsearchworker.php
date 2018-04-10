<?php

/**
 * Example of worker usage
 * 
 * */
class erLhcoreClassElasticSearchWorker {
     
    public function perform()
    {
        $db = ezcDbInstance::get();
        $db->reconnect(); // Because it timeouts automatically, this calls to reconnect to database, this is implemented in 2.52v

        $stmt = $db->prepare('SELECT chat_id FROM lhc_lheschat_index LIMIT :limit FOR UPDATE ');
        $stmt->bindValue(':limit',100,PDO::PARAM_INT);
        $stmt->execute();
        $chatsId = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($chatsId)) {

            $chats = erLhcoreClassModelChat::getList(array('filterin' => array('id' => $chatsId)));

            if (!empty($chats)) {
                erLhcoreClassElasticSearchIndex::indexChats(array('chats' => $chats));
            }

            // Delete indexed chat's records
            $stmt = $db->prepare('DELETE FROM lhc_lheschat_index WHERE chat_id IN (' . implode(',', $chatsId) . ')');
            $stmt->execute();
        }

        if (count($chatsId) == 100) {
            erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionLhcphpresque')->enqueue('lhc_elastic_queue', 'erLhcoreClassElasticSearchWorker', array());
        }
    }
}

?>