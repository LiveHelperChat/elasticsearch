<?php

/**
 * Example of worker usage
 * 
 * */
#[\AllowDynamicProperties]
class erLhcoreClassElasticSearchWorker {
     
    public function perform()
    {
        $db = ezcDbInstance::get();
        $db->reconnect(); // Because it timeouts automatically, this calls to reconnect to database, this is implemented in 2.52v

        $esOptions = erLhcoreClassChat::getSession()->load( 'erLhcoreClassModelChatConfig', 'elasticsearch_options' );
        $data = (array)$esOptions->data;

        if (isset($data['disable_es']) && $data['disable_es'] == 1) {
            error_log('Elastic search disabled in erLhcoreClassElasticSearchWorker');
            return;
        }

        $db->beginTransaction();
        try {
            $stmt = $db->prepare('SELECT chat_id FROM lhc_lheschat_index WHERE status = 0 LIMIT :limit FOR UPDATE ');
            $stmt->bindValue(':limit',100,PDO::PARAM_INT);
            $stmt->execute();
            $chatsId = $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e) {
            // Someone is already processing. So we just ignore and retry later
            return;
        }

        if (!empty($chatsId)) {
            // Delete indexed chat's records
            $stmt = $db->prepare('UPDATE lhc_lheschat_index SET status = 1 WHERE chat_id IN (' . implode(',', $chatsId) . ')');
            $stmt->execute();
            $db->commit();

            $chats = erLhcoreClassModelChat::getList(array('filterin' => array('id' => $chatsId)));

            if (!empty($chats)) {
                try {
                    erLhcoreClassElasticSearchIndex::indexChats(array('chats' => $chats));
                } catch (Exception $e) {
                    error_log($e->getMessage() . "\n" . $e->getTraceAsString());
                    return;
                }
            }

            $esOptions = erLhcoreClassChat::getSession()->load( 'erLhcoreClassModelChatConfig', 'elasticsearch_options' );
            $data = (array)$esOptions->data;

            if (isset($data['disable_es']) && $data['disable_es'] == 1) {
                error_log('Elastic search disabled in erLhcoreClassElasticSearchWorker');
                return;
            }

            $stmt = $db->prepare('DELETE FROM lhc_lheschat_index WHERE chat_id IN (' . implode(',', $chatsId) . ')');
            $stmt->execute();

        } else {
            $db->rollback();
        }

        $mailsIndexed = $mailsIndexedConversations = 0;

        if (!isset($data['disable_es_mail']) || $data['disable_es_mail'] == 0) {
            /*
             * Mails messages index
             * */
            $mailsIndexed = $this->indexMails();

            /*
             * Conversations index
             * */
            $mailsIndexedConversations = $this->indexConversations();

            /*
             * Conversations index
             * */
            $this->indexDeleteMail();
        }

        $maxRecords = max($mailsIndexed,$mailsIndexedConversations);

        // Just even that we are indexing something
        // So extensions can index their own things
        \erLhcoreClassChatEventDispatcher::getInstance()->dispatch('system.elastic_search.index_objects',array());

        if ((count($chatsId) >= 100 || $maxRecords == 20) && erLhcoreClassRedis::instance()->llen('resque:queue:lhc_elastic_queue') <= 4) {
            erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionLhcphpresque')->enqueue('lhc_elastic_queue', 'erLhcoreClassElasticSearchWorker', array());
        }
    }

    public function indexDeleteMail()
    {
        $db = ezcDbInstance::get();
        $db->reconnect();

        $db->beginTransaction();
        try {
            $stmt = $db->prepare('SELECT mail_id, udate FROM lhc_lhesmail_index WHERE status = 0 AND op = 3 LIMIT :limit FOR UPDATE ');
            $stmt->bindValue(':limit',20,PDO::PARAM_INT);
            $stmt->execute();
            $chatsIdMetas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $chatsId = [];
            $chatsTimes = [];
            foreach ($chatsIdMetas as $chatsIdMeta) {
                $chatsId[] = $chatsIdMeta['mail_id'];
                $chatsTimes[] = $chatsIdMeta['udate'];
            }

        } catch (Exception $e) {
            // Someone is already processing. So we just ignore and retry later
            return 0;
        }

        if (!empty($chatsId)) {
            // Delete indexed chat's records
            $stmt = $db->prepare('UPDATE lhc_lhesmail_index SET status = 1 WHERE mail_id IN (' . implode(',', $chatsId) . ') AND op = 3');
            $stmt->execute();
            $db->commit();

            $sparams = array();
            $sparams['body']['query']['bool']['must'][]['terms']['_id'] = $chatsId;
            $sparams['limit'] = 1000;

            $documentsReindexed = erLhcoreClassModelESMail::getList($sparams,array('date_index' => array('gte' => min($chatsTimes), 'lte' => max($chatsTimes))));

            // Remove deleted documents
            foreach ($documentsReindexed as $document) {
                $document->removeThis();
            }

            $esOptions = erLhcoreClassChat::getSession()->load( 'erLhcoreClassModelChatConfig', 'elasticsearch_options' );
            $data = (array)$esOptions->data;

            if (isset($data['disable_es']) && $data['disable_es'] == 1) {
                error_log('Elastic search disabled in erLhcoreClassElasticSearchWorker');
                return 0;
            }

            $stmt = $db->prepare('DELETE FROM lhc_lhesmail_index WHERE mail_id IN (' . implode(',', $chatsId) . ') AND op = 3');
            $stmt->execute();

        } else {
            $db->rollback();
        }

        return count($chatsId);
    }

    public function indexConversations()
    {
        $db = ezcDbInstance::get();
        $db->reconnect();

        $db->beginTransaction();
        try {
            $stmt = $db->prepare('SELECT mail_id FROM lhc_lhesmail_index WHERE status = 0 AND op = 1 LIMIT :limit FOR UPDATE ');
            $stmt->bindValue(':limit',20,PDO::PARAM_INT);
            $stmt->execute();
            $chatsId = $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e) {
            // Someone is already processing. So we just ignore and retry later
            return 0;
        }

        if (!empty($chatsId)) {
            // Delete indexed chat's records
            $stmt = $db->prepare('UPDATE lhc_lhesmail_index SET status = 1 WHERE mail_id IN (' . implode(',', $chatsId) . ') AND op = 1');
            $stmt->execute();
            $db->commit();

            // This is conversation
            $mails = erLhcoreClassModelMailconvMessage::getList(array('filterin' => array('conversation_id' => $chatsId)));

            if (!empty($mails)) {
                try {
                    erLhcoreClassElasticSearchIndex::indexMails(array('mails' => $mails));
                } catch (Exception $e) {
                    error_log($e->getMessage() . "\n" . $e->getTraceAsString());
                    return 0;
                }
            }

            $esOptions = erLhcoreClassChat::getSession()->load( 'erLhcoreClassModelChatConfig', 'elasticsearch_options' );
            $data = (array)$esOptions->data;

            if (isset($data['disable_es']) && $data['disable_es'] == 1) {
                error_log('Elastic search disabled in erLhcoreClassElasticSearchWorker');
                return 0;
            }

            $stmt = $db->prepare('DELETE FROM lhc_lhesmail_index WHERE mail_id IN (' . implode(',', $chatsId) . ') AND op = 1');
            $stmt->execute();

        } else {
            $db->rollback();
        }

        return count($chatsId);
    }

    public function indexMails()
    {
        $db = ezcDbInstance::get();
        $db->reconnect();

        $db->beginTransaction();
        try {
            $stmt = $db->prepare('SELECT mail_id FROM lhc_lhesmail_index WHERE status = 0 AND op = 0 LIMIT :limit FOR UPDATE ');
            $stmt->bindValue(':limit',20,PDO::PARAM_INT);
            $stmt->execute();
            $chatsId = $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e) {
            // Someone is already processing. So we just ignore and retry later
            return 0;
        }

        if (!empty($chatsId)) {
            // Delete indexed chat's records
            $stmt = $db->prepare('UPDATE lhc_lhesmail_index SET status = 1 WHERE mail_id IN (' . implode(',', $chatsId) . ') AND op = 0');
            $stmt->execute();
            $db->commit();

            $mails = erLhcoreClassModelMailconvMessage::getList(array('filterin' => array('id' => $chatsId)));

            if (!empty($mails)) {
                try {
                    erLhcoreClassElasticSearchIndex::indexMails(array('mails' => $mails));
                } catch (Exception $e) {
                    error_log($e->getMessage() . "\n" . $e->getTraceAsString());
                    return 0;
                }
            }

            $esOptions = erLhcoreClassChat::getSession()->load( 'erLhcoreClassModelChatConfig', 'elasticsearch_options' );
            $data = (array)$esOptions->data;

            if (isset($data['disable_es']) && $data['disable_es'] == 1) {
                error_log('Elastic search disabled in erLhcoreClassElasticSearchWorker');
                return 0;
            }

            $stmt = $db->prepare('DELETE FROM lhc_lhesmail_index WHERE mail_id IN (' . implode(',', $chatsId) . ') AND op = 0');
            $stmt->execute();

        } else {
            $db->rollback();
        }

        return count($chatsId);
    }

}

?>