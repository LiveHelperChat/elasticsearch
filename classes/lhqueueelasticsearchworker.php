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
            $stmt->bindValue(':limit',50,PDO::PARAM_INT);
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
                    $response = erLhcoreClassElasticSearchIndex::indexChats(array('chats' => $chats));
                    foreach ($response as $indexItem) {
                        if (isset($indexItem['errors']) && $indexItem['errors'] > 0) {
                            foreach ($indexItem['items'] as $item) {
                                if (isset($item['index']['error'])) {
                                    erLhcoreClassLog::write( 'Chat index error - ' . json_encode($item['index']['error']),
                                        ezcLog::SUCCESS_AUDIT,
                                        array(
                                            'source' => 'lhc',
                                            'category' => 'resque_fatal',
                                            'line' => 0,
                                            'file' => 0,
                                            'object_id' => $item['index']['_id']
                                        )
                                    );
                                    $indexRemove = array_search($item['index']['_id'],$chatsId);
                                    if ($indexRemove !== false) {
                                        unset($chatsId[$indexRemove]);
                                    }
                                }
                            }
                        }
                    }
                } catch (Exception $e) {
                    // Try to log error to DB
                    try {
                        erLhcoreClassLog::write( implode(',',$chatsId) . "\n" . $e->getTraceAsString() . "\n" . $e->getMessage(),
                            ezcLog::SUCCESS_AUDIT,
                            array(
                                'source' => 'lhc',
                                'category' => 'resque_fatal',
                                'line' => 0,
                                'file' => 0,
                                'object_id' => 0
                            )
                        );
                    } catch (Exception $e) {

                    }
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

            if (!empty($chatsId)) {
                $stmt = $db->prepare('DELETE FROM lhc_lheschat_index WHERE chat_id IN (' . implode(',', $chatsId) . ')');
                $stmt->execute();
            }

        } else {
            $db->rollback();
        }

        $indexedOnlineVisitors = 0;
        if (isset($data['use_es_ov']) && $data['use_es_ov'] == 1) {
            $indexedOnlineVisitors = $this->indexOnlineVisitors();
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

        if ((count($chatsId) >= 50 || $maxRecords == 10 || $indexedOnlineVisitors == 50) && erLhcoreClassRedis::instance()->llen('resque:queue:lhc_elastic_queue') <= 4) {
            erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionLhcphpresque')->enqueue('lhc_elastic_queue', 'erLhcoreClassElasticSearchWorker', array('debug' => __CLASS__ . '::' . __FUNCTION__));
        }
    }

    public function indexOnlineVisitors()
    {
        $db = ezcDbInstance::get();
        $db->reconnect();

        $db->beginTransaction();

        try {
            $stmt = $db->prepare('SELECT online_user_id FROM lhc_lhesou_index WHERE status = 0 LIMIT :limit FOR UPDATE ');
            $stmt->bindValue(':limit',50,PDO::PARAM_INT);
            $stmt->execute();
            $chatsId = $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e) {
            // Someone is already processing. So we just ignore and retry later
            return 0;
        }

        if (!empty($chatsId)) {
            // Delete indexed chat's records
            $stmt = $db->prepare('UPDATE lhc_lhesou_index SET status = 1 WHERE online_user_id IN (' . implode(',', $chatsId) . ')');
            $stmt->execute();
            $db->commit();

            $onlineVisitors = erLhcoreClassModelChatOnlineUser::getList(array('filterin' => array('id' => $chatsId)));

            if (!empty($onlineVisitors)) {
                try {
                    $response = erLhcoreClassElasticSearchIndex::indexOnlineVisitors(array('items' => $onlineVisitors));
                    foreach ($response as $indexItem) {
                        if (isset($indexItem['errors']) && $indexItem['errors'] > 0) {
                            foreach ($indexItem['items'] as $item) {
                                if (isset($item['index']['error'])) {
                                    erLhcoreClassLog::write( 'Online visitor index error - ' . json_encode($item['index']['error']),
                                        ezcLog::SUCCESS_AUDIT,
                                        array(
                                            'source' => 'lhc',
                                            'category' => 'resque_fatal',
                                            'line' => 0,
                                            'file' => 0,
                                            'object_id' => $item['index']['_id']
                                        )
                                    );
                                    $indexRemove = array_search($item['index']['_id'],$chatsId);
                                    if ($indexRemove !== false) {
                                        unset($chatsId[$indexRemove]);
                                    }
                                }
                            }
                        }
                    }
                } catch (Exception $e) {
                    // Try to log error to DB
                    try {
                        erLhcoreClassLog::write( implode(',',$chatsId) . "\n" . $e->getTraceAsString() . "\n" . $e->getMessage(),
                            ezcLog::SUCCESS_AUDIT,
                            array(
                                'source' => 'lhc',
                                'category' => 'resque_fatal',
                                'line' => 0,
                                'file' => 0,
                                'object_id' => 0
                            )
                        );
                    } catch (Exception $e) {

                    }
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

            if (!empty($chatsId)) {
                $stmt = $db->prepare('DELETE FROM lhc_lhesou_index WHERE online_user_id IN (' . implode(',', $chatsId) . ')');
                $stmt->execute();
            }

        } else {
            $db->rollback();
        }

        return count($chatsId);
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
            $stmt->bindValue(':limit',10,PDO::PARAM_INT);
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
            $mails = erLhcoreClassModelMailconvMessage::getList(array('limit' => 1000, 'filterin' => array('conversation_id' => $chatsId)));
            if (!empty($mails)) {
                try {
                    $parts = ceil(count($mails) / 20); // We keep same limit as standard index
                    for ($i = 0; $i < $parts; $i++) { // Some conversations can be abusively large
                        $mailsIndex = array_slice($mails,$i * 20,20, true);
                        $response = erLhcoreClassElasticSearchIndex::indexMails(array('mails' => $mailsIndex));
                        foreach ($response as $indexItem) {
                            if (isset($indexItem['errors']) && $indexItem['errors'] > 0) {
                                foreach ($indexItem['items'] as $item) {
                                    if (isset($item['index']['error'])) {
                                        erLhcoreClassLog::write( 'Mail conversation index error - ' . json_encode($item['index']['error']),
                                            ezcLog::SUCCESS_AUDIT,
                                            array(
                                                'source' => 'lhc',
                                                'category' => 'resque_fatal',
                                                'line' => 0,
                                                'file' => 0,
                                                'object_id' => $item['index']['_id']
                                            )
                                        );
                                        foreach ($mailsIndex as $mail) {
                                            if ($mail->id == $item['index']['_id']) {
                                                $indexResult = array_search($mail->conversation_id,$chatsId);
                                                if ($indexResult !== false) {
                                                    unset($chatsId[$indexResult]); // This time we look for conversation
                                                }
                                                break;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                } catch (Exception $e) {
                    try {
                        erLhcoreClassLog::write( implode(',',$chatsId) . "\n" . $e->getTraceAsString() . "\n" . $e->getMessage(),
                            ezcLog::SUCCESS_AUDIT,
                            array(
                                'source' => 'lhc',
                                'category' => 'resque_fatal',
                                'line' => 0,
                                'file' => 0,
                                'object_id' => 0
                            )
                        );
                    } catch (Exception $e) {

                    }
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

            if (!empty($chatsId)) {
                $stmt = $db->prepare('DELETE FROM lhc_lhesmail_index WHERE mail_id IN (' . implode(',', $chatsId) . ') AND op = 1');
                $stmt->execute();
            }

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
                    $response = erLhcoreClassElasticSearchIndex::indexMails(array('mails' => $mails));
                    foreach ($response as $indexItem) {
                        if (isset($indexItem['errors']) && $indexItem['errors'] > 0) {
                            foreach ($indexItem['items'] as $item) {
                                if (isset($item['index']['error'])) {
                                    erLhcoreClassLog::write( 'Mail message index error - ' . json_encode($item['index']['error']),
                                        ezcLog::SUCCESS_AUDIT,
                                        array(
                                            'source' => 'lhc',
                                            'category' => 'resque_fatal',
                                            'line' => 0,
                                            'file' => 0,
                                            'object_id' => $item['index']['_id']
                                        )
                                    );
                                    $indexRemove = array_search($item['index']['_id'],$chatsId);
                                    if ($indexRemove !== false) {
                                        unset($chatsId[$indexRemove]);
                                    }
                                }
                            }
                        }
                    }
                } catch (Exception $e) {
                    // Try to log error to DB
                    try {
                        erLhcoreClassLog::write( implode(',',$chatsId) . "\n" . $e->getTraceAsString() . "\n" . $e->getMessage(),
                            ezcLog::SUCCESS_AUDIT,
                            array(
                                'source' => 'lhc',
                                'category' => 'resque_fatal',
                                'line' => 0,
                                'file' => 0,
                                'object_id' => 0
                            )
                        );
                    } catch (Exception $e) {

                    }
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

            if (!empty($chatsId)) {
                $stmt = $db->prepare('DELETE FROM lhc_lhesmail_index WHERE mail_id IN (' . implode(',', $chatsId) . ') AND op = 0');
                $stmt->execute();
            }

        } else {
            $db->rollback();
        }

        return count($chatsId);
    }

}

?>