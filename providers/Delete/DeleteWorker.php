<?php

namespace LiveHelperChatExtension\elasticsearch\providers\Delete;
#[\AllowDynamicProperties]
class DeleteWorker
{
    public function perform()
    {
        $db = \ezcDbInstance::get();
        $db->reconnect(); // Because it timeouts automatically, this calls to reconnect to database, this is implemented in 2.52v

        $db->beginTransaction();
        try {
            $stmt = $db->prepare('SELECT `conversation_id`,`filter_id`,`index` FROM `lhc_mailconv_delete_item_elastic` WHERE `status` = 0 LIMIT :limit FOR UPDATE ');
            $stmt->bindValue(':limit',20,\PDO::PARAM_INT);
            $stmt->execute();
            $chatsId = [];
            $chatsIdFilter = [];
            $chatsIdIndex = [];
            foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $itemArchive) {
                $chatsId[] = $itemArchive['conversation_id'];
                $chatsIdFilter[$itemArchive['conversation_id']] = $itemArchive['filter_id'];
                $chatsIdIndex[$itemArchive['conversation_id']] = $itemArchive['index'];
            }

        } catch (\Exception $e) {
            // Someone is already processing. So we just ignore and retry later
            return;
        }

        $filters = DeleteFilter::getList(['filterin' => ['id' => array_unique($chatsIdFilter)]]);

        $archiveIds = [];
        foreach ($filters as $filter) {
            if ($filter->archive_id > 0) {
                $archiveIds[] = $filter->archive_id;
            }
        }

        $archives = [];
        if (!empty($archiveIds)) {
            $archives = \LiveHelperChat\Models\mailConv\Archive\Range::getList(['filterin' => ['id' => array_unique($archiveIds)]]);
        }

        if (!empty($chatsId)) {
            // Delete indexed chat's records
            $stmt = $db->prepare('UPDATE `lhc_mailconv_delete_item_elastic` SET `status` = 1 WHERE `conversation_id` IN (' . implode(',', $chatsId) . ')');
            $stmt->execute();
            $db->commit();

            // We need to figureout conversations for the messages
            $stmt = $db->prepare('SELECT `conversation_id`,`id` FROM `lhc_mailconv_msg` WHERE `id` IN (' . implode(',',$chatsId) . ')');
            $stmt->execute();
            $messageConversation = [];
            foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $itemConversation) {
                $messageConversation[$itemConversation['id']] = $itemConversation['conversation_id'];
            }

            $conversations = [];
            if (!empty($messageConversation)) {
                $conversations = \erLhcoreClassModelMailconvConversation::getList(array('filterin' => array('id' => $messageConversation)));
            }

            try {

                foreach ($conversations as $conversation) {
                    $key = array_search($conversation->id, $messageConversation);
                    if (isset($filters[$chatsIdFilter[$key]]) && isset($archives[$filters[$chatsIdFilter[$key]]->archive_id])) {
                        $archives[$filters[$chatsIdFilter[array_search($conversation->id, $messageConversation)]]->archive_id]->process([$conversation]);
                    } else {
                        $conversation->removeThis();
                    }
                }

                foreach ($chatsId as $messageId) {

                    // Try remove directly if it still exists
                    $messageDb = \erLhcoreClassModelMailconvMessage::fetch($messageId);

                    if ($messageDb instanceof \erLhcoreClassModelMailconvMessage) {
                        try {
                            $messageDb->removeThis();
                        } catch (\Exception $e) {
                            try {
                                \erLhcoreClassLog::write( implode(',',$chatsId) . "\n" . $e->getTraceAsString() . "\n" . $e->getMessage(),
                                    \ezcLog::SUCCESS_AUDIT,
                                    array(
                                        'source' => 'lhc',
                                        'category' => 'resque_fatal',
                                        'line' => 0,
                                        'file' => 0,
                                        'object_id' => 0
                                    )
                                );
                            } catch (\Exception $e) {

                            }
                        }
                    }

                    $messageEs = \erLhcoreClassModelESMail::fetch($messageId, $chatsIdIndex[$messageId]);
                    if ($messageEs instanceof \erLhcoreClassModelESMail) {
                        try {
                            $messageEs->removeThis();
                        } catch (\Exception $e) {
                            try {
                                \erLhcoreClassLog::write( implode(',',$chatsId) . "\n" . $e->getTraceAsString() . "\n" . $e->getMessage(),
                                    \ezcLog::SUCCESS_AUDIT,
                                    array(
                                        'source' => 'lhc',
                                        'category' => 'resque_fatal',
                                        'line' => 0,
                                        'file' => 0,
                                        'object_id' => 0
                                    )
                                );
                            } catch (\Exception $e) {

                            }
                        }
                    }
                }

            } catch (\Exception $e) {
                // Try to log error to DB
                try {
                    \erLhcoreClassLog::write( implode(',',$chatsId) . "\n" . $e->getTraceAsString() . "\n" . $e->getMessage(),
                        \ezcLog::SUCCESS_AUDIT,
                        array(
                            'source' => 'lhc',
                            'category' => 'resque_fatal',
                            'line' => 0,
                            'file' => 0,
                            'object_id' => 0
                        )
                    );
                } catch (\Exception $e) {

                }
                error_log($e->getMessage() . "\n" . $e->getTraceAsString());
                return;
            }

            $stmt = $db->prepare('DELETE FROM `lhc_mailconv_delete_item_elastic` WHERE `conversation_id` IN (' . implode(',', $chatsId) . ')');
            $stmt->execute();

        } else {
            $db->rollback();
        }

        if (isset($this->args['is_background']) && count($chatsId) >= 20 && \erLhcoreClassRedis::instance()->llen('resque:queue:lhc_mailconv_delete_elastic') <= 4) {
            \erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionLhcphpresque')->enqueue('lhc_mailconv_delete_elastic', '\LiveHelperChatExtension\elasticsearch\providers\Delete\DeleteWorker', array('is_background' => true));
        }
    }
}
