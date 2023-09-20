<?php
#[\AllowDynamicProperties]
class erLhcoreClassElasticSearchIndex
{
    public static $ts = null;

    public static function indexChats($params)
    {
        $sparams = array();
        $sparams['body']['query']['bool']['must'][]['terms']['_id'] = array_keys($params['chats']);
        $sparams['limit'] = 1000;

        $dateRange = array();
        foreach ($params['chats'] as $item) {
            if ($item->time > 0) {
                $dateRange[] = $item->time;
            }
        }

        $documents = erLhcoreClassModelESChat::getList($sparams,array('date_index' => array('gte' => min($dateRange), 'lte' => max($dateRange))));

        $documentsReindexed = array();
        foreach ($documents as $document) {
            $documentsReindexed[$document->chat_id] = $document;
        }

        $objectsSave = array();

        $esOptions = erLhcoreClassModelChatConfig::fetch('elasticsearch_options');
        $dataOptions = (array)$esOptions->data;

        $settings = include ('extension/elasticsearch/settings/settings.ini.php');

        if (isset($dataOptions['check_if_exists']) && $dataOptions['check_if_exists'] == 1)
        {
            $dateRangesIndex = [];
            foreach ($dateRange as $dateRangeItem) {
                if ($dataOptions['index_type'] == 'daily') {
                    $dateRangesIndex[] = date('Y.m.d',$dateRangeItem);
                } elseif ($dataOptions['index_type'] == 'yearly') {
                    $dateRangesIndex[] = date('Y',$dateRangeItem);
                } elseif ($dataOptions['index_type'] == 'monthly') {
                    $dateRangesIndex[] = date('Y.m',$dateRangeItem);
                }
            }

            if (!empty($dateRangesIndex)) {
                foreach (array_unique($dateRangesIndex) as $indexPrepend)
                {
                    $sessionElasticStatistic = erLhcoreClassModelESChat::getSession();
                    $esSearchHandler = erLhcoreClassElasticClient::getHandler();
                    erLhcoreClassElasticClient::indexExists($esSearchHandler, $settings['index'], $indexPrepend, true);
                }
            }
        }

        $attributesGet = [];
        if (isset($settings['columns']) && !empty($settings['columns'])) {
            foreach ($settings['columns'] as $columnAttr => $column) {
                if ($column['enabled'] && isset($column['content'])) {
                    $attributesGet[$columnAttr] = $column;
                }
            }
        }

        foreach ($params['chats'] as $keyValue => $item) {
            if (isset($documentsReindexed[$keyValue])) {
                $esChat = $documentsReindexed[$keyValue];
            } else {
                $esChat = new erLhcoreClassModelESChat();
            }

            $esChat->id = $item->id;
            $esChat->chat_id = $item->id;
            $esChat->time = $item->time * 1000;
            $esChat->pnd_time = $item->pnd_time * 1000;
            $esChat->cls_time = $item->cls_time * 1000;

            if ($item->ip != '') {
                $firstIp = explode(',',str_replace(' ','',$item->ip))[0];
                if (filter_var($firstIp, FILTER_VALIDATE_IP)) { // chat ip was not anonymized
                    $esChat->ip = $firstIp;
                }
            }

            $esChat->user_id = $item->user_id;

            if (! empty($item->lon) && ! empty($item->lat)) {
                $esChat->location = array(
                    (float) $item->lon,
                    (float) $item->lat
                );
            }

            $esChat->dep_id = $item->dep_id;
            $esChat->city = $item->city;
            $partsCity = explode('||',$esChat->city);
            if (isset($partsCity[1]) && !empty($partsCity[1])) {
                $esChat->region = trim($partsCity[1]);
            }
            $esChat->wait_time = $item->wait_time;
            $esChat->nick = $item->nick;
            $esChat->nick_keyword = $item->nick;
            $esChat->status = $item->status;
            $esChat->hash = $item->hash;
            $esChat->referrer = $item->referrer;
            $esChat->user_status = $item->user_status;
            $esChat->support_informed = $item->support_informed;
            $esChat->email = $item->email;
            $esChat->country_code = $item->country_code;
            $esChat->country_name = $item->country_name;
            $esChat->phone = $item->phone;
            $esChat->has_unread_messages = $item->has_unread_messages;
            $esChat->last_user_msg_time = $item->last_user_msg_time;
            $esChat->last_msg_id = $item->last_msg_id;
            $esChat->additional_data = $item->additional_data;
            $esChat->mail_send = $item->mail_send;
            $esChat->session_referrer = $item->session_referrer;
            $esChat->chat_duration = $item->chat_duration;
            $esChat->chat_variables = $item->chat_variables;
            $esChat->priority = $item->priority;
            $esChat->chat_initiator = $item->chat_initiator;
            $esChat->online_user_id = $item->online_user_id;
            $esChat->transfer_timeout_ts = $item->transfer_timeout_ts;
            $esChat->transfer_timeout_ac = $item->transfer_timeout_ac;
            $esChat->transfer_if_na = $item->transfer_if_na;
            $esChat->na_cb_executed = $item->na_cb_executed;
            $esChat->fbst = $item->fbst;
            $esChat->nc_cb_executed = $item->nc_cb_executed;
            $esChat->operator_typing_id = $item->operator_typing_id;
            $esChat->remarks = $item->remarks;
            $esChat->status_sub = $item->status_sub;
            $esChat->operation = $item->operation;
            $esChat->screenshot_id = $item->screenshot_id;
            $esChat->unread_messages_informed = $item->unread_messages_informed;
            $esChat->reinform_timeout = $item->reinform_timeout;
            $esChat->has_unread_op_messages = $item->has_unread_op_messages;
            $esChat->user_closed_ts = $item->user_closed_ts;
            $esChat->chat_locale = $item->chat_locale;
            $esChat->chat_locale_to = $item->chat_locale_to;
            $esChat->unanswered_chat = $item->unanswered_chat;
            $esChat->product_id = $item->product_id;
            $esChat->last_op_msg_time = $item->last_op_msg_time;
            $esChat->unread_op_messages_informed = $item->unread_op_messages_informed;
            $esChat->status_sub_sub = $item->status_sub_sub;
            $esChat->status_sub_arg = $item->status_sub_arg;
            $esChat->uagent = $item->uagent;
            $esChat->device_type = $item->device_type;
            $esChat->sender_user_id = $item->sender_user_id;
            $esChat->user_tz_identifier = $item->user_tz_identifier;
            $esChat->operation_admin = $item->operation_admin;
            $esChat->tslasign = $item->tslasign;
            $esChat->transfer_uid = $item->transfer_uid;
            $esChat->usaccept = $item->usaccept;
            $esChat->lsync = $item->lsync;
            $esChat->auto_responder_id = $item->auto_responder_id;
            $esChat->invitation_id = $item->invitation_id;
            $esChat->gbot_id = $item->gbot_id;
            $esChat->iwh_id = $item->iwh_id;
            $esChat->abnd = ($item->lsync < ($item->pnd_time + $item->wait_time) && $item->wait_time > 1) || ($item->lsync > ($item->pnd_time + $item->wait_time) && $item->wait_time > 1 && $item->user_id == 0) ? 1 : 0;
            $esChat->drpd = $item->lsync > ($item->pnd_time + $item->wait_time) && $item->has_unread_op_messages == 1 && $item->user_id > 0 ? 1 : 0;

            $esChat->cls_us = $item->cls_us;
            $esChat->subject_id = [];

            $db = ezcDbInstance::get();
            $stmt = $db->prepare("SELECT `subject_id` FROM `lh_abstract_subject_chat` WHERE `chat_id` = :chat_id");
            $stmt->bindValue(':chat_id', $item->id,PDO::PARAM_INT);
            $stmt->execute();
            $subjectIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
            if (!empty($subjectIds)) {
                foreach ($subjectIds as $subjectId) {
                    $esChat->subject_id[] = (int)$subjectId;
                }
            }

            // Extensions can append custom value
            erLhcoreClassChatEventDispatcher::getInstance()->dispatch('elasticsearch.indexchat', array(
                'chat' => & $esChat
            ));

            // Store hour as UTC for easier grouping
            $date_utc = new \DateTime("", new \DateTimeZone("UTC"));
            $date_utc->setTimestamp($item->time);
            $esChat->hour = $date_utc->format("H");

            if (isset($params['archive']) && $params['archive'] == true) {
                $messagesChat = erLhcoreClassModelChatArchiveMsg::getList(array('limit' => 5000, 'filter' => array('chat_id' => $item->id)));
            } else {
                $messagesChat = erLhcoreClassModelmsg::getList(array('limit' => 5000, 'filter' => array('chat_id' => $item->id)));
            }

            $esChat->msg_visitor = null;
            $esChat->msg_operator = null;
            $esChat->msg_system = null;

            foreach ($messagesChat as $messageChat) {
                if ($messageChat->user_id == 0) {
                    $esChat->msg_visitor .= $messageChat->msg . "\n";
                } elseif ($messageChat->user_id > 0) {
                    $esChat->msg_operator .= $messageChat->msg . "\n";
                } else {
                    $esChat->msg_system .= $messageChat->msg . "\n";
                }
            }

            $esChat->msg_system = trim((string)$esChat->msg_system);
            $esChat->msg_operator = trim((string)$esChat->msg_operator);
            $esChat->msg_visitor = trim((string)$esChat->msg_visitor);

            // Has visitor file
            $esChat->hvf = preg_match('/\[file="?(.*?)"?\]/is',$esChat->msg_visitor);

            // Hast operator file
            $esChat->hof = preg_match('/\[file="?(.*?)"?\]/is',$esChat->msg_operator);

            // Fields defined from settings file
            foreach ($attributesGet as $attributeField => $attributeGet) {
                $esChat->{$attributeField} = erLhcoreClassGenericBotWorkflow::translateMessage($attributeGet['content'], array('chat' => $item, 'args' => ['chat' => $item]));

                if ($attributeGet['type'] == 'keyword') {
                    if (trim($esChat->{$attributeField}) == '') {
                        $esChat->{$attributeField} = null;
                    } else {
                        $esChat->{$attributeField} = (string)$esChat->{$attributeField};
                    }
                }

                if ($attributeGet['type'] == 'float') {
                    if (trim($esChat->{$attributeField}) == '') {
                        $esChat->{$attributeField} = null;
                    } else {
                        $esChat->{$attributeField} = (double)$esChat->{$attributeField};
                    }
                }
            }

            // Let indexes to know custom fields
            $esChat->setCustomGetAttributes(array_keys($attributesGet));

            $indexSave = erLhcoreClassModelESChat::$indexName . '-' . erLhcoreClassModelESChat::$elasticType;

            if (isset($esChat->meta_data['index']) && $esChat->meta_data['index'] != '') {
                $indexSave = $esChat->meta_data['index'];
            } else if (isset($dataOptions['index_type'])) {
                if ($dataOptions['index_type'] == 'daily') {
                    $indexSave = erLhcoreClassModelESChat::$indexName . '-' .erLhcoreClassModelESChat::$elasticType . '-' . gmdate('Y.m.d',$item->time);
                } elseif ($dataOptions['index_type'] == 'yearly') {
                    $indexSave = erLhcoreClassModelESChat::$indexName . '-' .erLhcoreClassModelESChat::$elasticType . '-' . gmdate('Y',$item->time);
                } elseif ($dataOptions['index_type'] == 'monthly') {
                    $indexSave = erLhcoreClassModelESChat::$indexName . '-' .erLhcoreClassModelESChat::$elasticType . '-' . gmdate('Y.m',$item->time);
                }
            }

            $objectsSave[$indexSave][] = $esChat;
        }

        erLhcoreClassModelESChat::bulkSave($objectsSave, array('custom_index' => true, 'ignore_id' => true));
    }

    public static function indexOs($params)
    {
        if (empty($params['items'])) {
            return;
        }

        $dateRange = array();
        foreach ($params['items'] as $item) {
            if ($item->time > 0) {
                $dateRange[] = $item->time;
            }
        }

        $sparams = array();
        $sparams['body']['query']['bool']['must'][]['terms']['_id'] = array_keys($params['items']);
        $sparams['limit'] = 1000;
        $documents = erLhcoreClassModelESOnlineSession::getList($sparams, array('date_index' => array('gte' => min($dateRange), 'lte' => max($dateRange))));

        $documentsReindexed = array();
        foreach ($documents as $document) {
            $documentsReindexed[$document->os_id] = $document;
        }

        $esOptions = erLhcoreClassModelChatConfig::fetch('elasticsearch_options');
        $dataOptions = (array)$esOptions->data;

        $objectsSave = array();

        erLhcoreClassModelESOnlineSession::getSession();

        foreach ($params['items'] as $keyValue => $item) {
            if (isset($documentsReindexed[$keyValue])) {
                $osLog = $documentsReindexed[$keyValue];
            } else {
                $osLog = new erLhcoreClassModelESOnlineSession();
                $osLog->id = $item->id;
                $osLog->user_id = $item->user_id;
                $osLog->os_id = $item->id;
                $osLog->time = $item->time * 1000;
            }

            $osLog->lactivity = $item->lactivity * 1000;
            $osLog->duration = $item->duration;

            $indexSave = erLhcoreClassModelESOnlineSession::$indexName . '-' . erLhcoreClassModelESOnlineSession::$elasticType;

            if (isset($osLog->meta_data['index']) && $osLog->meta_data['index'] != '') {
                $indexSave = $osLog->meta_data['index'];
            } else if (isset($dataOptions['index_type'])) {
                if ($dataOptions['index_type'] == 'daily') {
                    $indexSave = erLhcoreClassModelESOnlineSession::$indexName . '-' . erLhcoreClassModelESOnlineSession::$elasticType . '-' . date('Y.m.d',$item->time);
                } elseif ($dataOptions['index_type'] == 'yearly') {
                    $indexSave = erLhcoreClassModelESOnlineSession::$indexName . '-' . erLhcoreClassModelESOnlineSession::$elasticType . '-' . date('Y',$item->time);
                } elseif ($dataOptions['index_type'] == 'monthly') {
                    $indexSave = erLhcoreClassModelESOnlineSession::$indexName . '-' . erLhcoreClassModelESOnlineSession::$elasticType . '-' . date('Y.m',$item->time);
                }
            }

            $objectsSave[$indexSave][] = $osLog;
        }

        erLhcoreClassModelESOnlineSession::bulkSave($objectsSave, array('custom_index' => true, 'ignore_id' => true));
    }

    public static function indexChatDelay($params)
    {
        $esOptions = erLhcoreClassModelChatConfig::fetch('elasticsearch_options');
        $dataOptions = (array)$esOptions->data;

        $db = ezcDbInstance::get();
        $stmt = $db->prepare('INSERT IGNORE INTO lhc_lheschat_index (`chat_id`) VALUES (:chat_id)');
        $stmt->bindValue(':chat_id', $params['chat']->id, PDO::PARAM_STR);
        $stmt->execute();

        // Schedule background worker for instant indexing
        if (isset($dataOptions['use_php_resque']) && $dataOptions['use_php_resque'] == 1) {
            erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionLhcphpresque')->enqueue('lhc_elastic_queue', 'erLhcoreClassElasticSearchWorker', array());
        }
    }

    public static function indexChatModify($params)
    {
        if ($params['chat']->status == erLhcoreClassModelChat::STATUS_CLOSED_CHAT) {
            self::indexChatDelay($params);
        }
    }

    public static function indexChatDelete($params)
    {
        $sparams['body']['query']['bool']['must'][]['term']['chat_id'] = $params['chat']->id;

        $chat = erLhcoreClassModelESChat::findOne(array(
            'offset' => 0,
            'limit' => 0,
            'body' => $sparams['body']
        ),
            array('date_index' => array('gte' => ($params['chat']->time - (31*24*3600)))));

        if ($chat instanceof erLhcoreClassModelESChat) {
            $chat->removeThis();
        }
    }

    public static function indexPendingChats($params)
    {
        $esOptions = erLhcoreClassModelChatConfig::fetch('elasticsearch_options');
        $dataOptions = (array)$esOptions->data;

        $items = $params['items'];

        $ts = erLhcoreClassElasticSearchIndex::$ts !== null ? erLhcoreClassElasticSearchIndex::$ts*1000 : time()*1000;

        $objectsSave = array();

        erLhcoreClassModelESPendingChat::getSession();

        foreach ($items as $keyValue => $item) {
            $esChat = new erLhcoreClassModelESPendingChat();
            $esChat->chat_id = $item->id;
            $esChat->time = $item->time * 1000;
            $esChat->itime = $ts;
            $esChat->dep_id = $item->dep_id;
            $esChat->status = $item->status;

            $indexSave = erLhcoreClassModelESPendingChat::$indexName . '-' . erLhcoreClassModelESPendingChat::$elasticType;

            if (isset($dataOptions['index_type'])) {
                if ($dataOptions['index_type'] == 'daily') {
                    $indexSave = erLhcoreClassModelESPendingChat::$indexName . '-' . erLhcoreClassModelESPendingChat::$elasticType . '-' . date('Y.m.d',$item->time);
                } elseif ($dataOptions['index_type'] == 'yearly') {
                    $indexSave = erLhcoreClassModelESPendingChat::$indexName . '-' . erLhcoreClassModelESPendingChat::$elasticType . '-' .  date('Y',$item->time);
                } elseif ($dataOptions['index_type'] == 'monthly') {
                    $indexSave = erLhcoreClassModelESPendingChat::$indexName . '-' . erLhcoreClassModelESPendingChat::$elasticType . '-' .  date('Y.m',$item->time);
                }
            }

            $objectsSave[$indexSave][] = $esChat;
        }

        if (!empty($objectsSave)) {
            erLhcoreClassModelESPendingChat::bulkSave($objectsSave, array('custom_index' => true));
        }
    }

    public static function indexOnlineOperators()
    {
        $db = ezcDbInstance::get();

        $ts = erLhcoreClassElasticSearchIndex::$ts !== null ? erLhcoreClassElasticSearchIndex::$ts-60 : time()-60;

        $stmt = $db->prepare("SELECT user_id, dep_id FROM `lh_userdep` WHERE `last_activity` > :time and hide_online = 0 GROUP BY user_id, dep_id");
        $stmt->bindValue(':time', $ts, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $objectsSave = array();

        $ts = erLhcoreClassElasticSearchIndex::$ts !== null ? erLhcoreClassElasticSearchIndex::$ts * 1000 : time() * 1000;

        $saveObjects = array();
        foreach ($rows as $row) {
            $saveObjects[$row['user_id']]['dep_ids'][] = (int)$row['dep_id'];
        }

        $esOptions = erLhcoreClassModelChatConfig::fetch('elasticsearch_options');
        $dataOptions = (array)$esOptions->data;

        // To initialize indexes
        erLhcoreClassModelESOnlineOperator::getSession();

        foreach ($saveObjects as $userId => $data) {
            $opEs = new erLhcoreClassModelESOnlineOperator();
            $opEs->dep_ids = $data['dep_ids'];
            $opEs->user_id = $userId;
            $opEs->itime = $ts;

            $indexSave = erLhcoreClassModelESOnlineOperator::$indexName . '-' . erLhcoreClassModelESOnlineOperator::$elasticType;

            if (isset($dataOptions['index_type'])) {
                if ($dataOptions['index_type'] == 'daily') {
                    $indexSave = erLhcoreClassModelESOnlineOperator::$indexName . '-' . erLhcoreClassModelESOnlineOperator::$elasticType . '-' . date('Y.m.d', $opEs->itime/1000);
                } elseif ($dataOptions['index_type'] == 'yearly') {
                    $indexSave = erLhcoreClassModelESOnlineOperator::$indexName . '-' . erLhcoreClassModelESOnlineOperator::$elasticType . '-' . date('Y',$opEs->itime/1000);
                } elseif ($dataOptions['index_type'] == 'monthly') {
                    $indexSave = erLhcoreClassModelESOnlineOperator::$indexName . '-' . erLhcoreClassModelESOnlineOperator::$elasticType . '-' . date('Y.m',$opEs->itime/1000);
                }
            }

            $objectsSave[$indexSave][] = $opEs;
        }

        erLhcoreClassModelESOnlineOperator::bulkSave($objectsSave, array('custom_index' => true));
    }

    public static function indexMessages($params)
    {
        $items = $params['messages'];

        $chatsIds = array();
        $dateRange = array();
        foreach ($items as $item) {
            $chatsIds[] = $item->chat_id;
            if ($item->time > 0) {
                $dateRange[] = $item->time;
            }
        }

        if (empty($chatsIds)) {
            return;
        }

        $sql = "SELECT id, dep_id, user_id, gbot_id, status_sub FROM lh_chat WHERE id IN (" . implode(',', $chatsIds) . ')';

        $db = ezcDbInstance::get();
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $chatsData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $infoChat = array();

        foreach ($chatsData as $chatData) {
            $infoChat[$chatData['id']]['dep_id'] = $chatData['dep_id'];
            $infoChat[$chatData['id']]['user_id'] = $chatData['user_id'];
            $infoChat[$chatData['id']]['gbot_id'] = $chatData['gbot_id'];
            $infoChat[$chatData['id']]['status_sub'] = $chatData['status_sub'];
        }

        $sparams = array();
        $sparams['body']['query']['bool']['must'][]['terms']['msg_id'] = array_keys($params['messages']);
        $sparams['limit'] = 1000;

        $documents = erLhcoreClassModelESMsg::getList($sparams, array('date_index' => array('gte' => min($dateRange), 'lte' => max($dateRange))));

        $documentsReindexed = array();
        foreach ($documents as $document) {
            $documentsReindexed[$document->msg_id] = $document;
        }

        $objectsSave = array();

        $esOptions = erLhcoreClassModelChatConfig::fetch('elasticsearch_options');
        $dataOptions = (array)$esOptions->data;

        erLhcoreClassModelESMsg::getSession();

        foreach ($params['messages'] as $keyValue => $item) {

            if (isset($documentsReindexed[$keyValue])) {
                $esMsg = $documentsReindexed[$keyValue];
            } else {
                $esMsg = new erLhcoreClassModelESMsg();
            }

            if (isset($infoChat[$item->chat_id]['dep_id']) && isset($infoChat[$item->chat_id]['user_id']))
            {
                $esMsg->chat_id = $item->chat_id;
                $esMsg->msg_id = $item->id;
                $esMsg->msg = $item->msg;
                $esMsg->time = $item->time * 1000;
                $esMsg->name_support = $item->name_support;
                $esMsg->user_id = $item->user_id;
                $esMsg->del_st = $item->del_st;
                $esMsg->dep_id = (int)$infoChat[$item->chat_id]['dep_id'];
                $esMsg->op_user_id = (int)$infoChat[$item->chat_id]['user_id'];
                $esMsg->gbot_id = (int)$infoChat[$item->chat_id]['gbot_id'];
                $esMsg->status_sub = (int)$infoChat[$item->chat_id]['status_sub'];

                $indexSave = erLhcoreClassModelESMsg::$indexName . '-' . erLhcoreClassModelESMsg::$elasticType;

                if (isset($esMsg->meta_data['index']) && $esMsg->meta_data['index'] != '') {
                    $indexSave = $esMsg->meta_data['index'];
                } else if (isset($dataOptions['index_type'])) {
                    if ($dataOptions['index_type'] == 'daily') {
                        $indexSave = erLhcoreClassModelESMsg::$indexName . '-' . erLhcoreClassModelESMsg::$elasticType . '-' . date('Y.m.d', $item->time);
                    } elseif ($dataOptions['index_type'] == 'yearly') {
                        $indexSave = erLhcoreClassModelESMsg::$indexName . '-' . erLhcoreClassModelESMsg::$elasticType . '-' . date('Y',$item->time);
                    } elseif ($dataOptions['index_type'] == 'monthly') {
                        $indexSave = erLhcoreClassModelESMsg::$indexName . '-' . erLhcoreClassModelESMsg::$elasticType . '-' . date('Y.m',$item->time);
                    }
                }

                $objectsSave[$indexSave][] = $esMsg;
            }
        }

        if (!empty($objectsSave)) {
            erLhcoreClassModelESMsg::bulkSave($objectsSave, array('custom_index' => true));
        }
    }

    public static function indexParticipant($params)
    {
        $items = $params['participant'];

        $dateRange = array();
        $chatsIds = array();
        foreach ($items as $item) {
             $chatsIds[] = $item->chat_id;
             if ($item->time > 0) {
                $dateRange[] = $item->time;
            }
        }

        if (empty($chatsIds)) {
            return;
        }

        $chatsData = erLhcoreClassModelChat::getList(['limit' => false, 'filterin' => ['id' => $chatsIds]]);

        $sparams = array();
        $sparams['body']['query']['bool']['must'][]['terms']['_id'] = array_keys($params['participant']);
        $sparams['limit'] = 1000;

        $documents = erLhcoreClassModelESParticipant::getList($sparams, array('date_index' => array('gte' => min($dateRange), 'lte' => max($dateRange))));

        $documentsReindexed = array();
        foreach ($documents as $document) {
            $documentsReindexed[$document->id] = $document;
        }

        $objectsSave = array();

        $esOptions = erLhcoreClassModelChatConfig::fetch('elasticsearch_options');
        $dataOptions = (array)$esOptions->data;

        erLhcoreClassModelESParticipant::getSession();

        foreach ($params['participant'] as $keyValue => $item) {

            if (isset($documentsReindexed[$keyValue])) {
                $esMsg = $documentsReindexed[$keyValue];
            } else {
                $esMsg = new erLhcoreClassModelESParticipant();
            }

            $esMsg->id = $item->id;
            $esMsg->chat_id = $item->chat_id;
            $esMsg->user_id = $item->user_id;
            $esMsg->duration = $item->duration;
            $esMsg->time = $item->time * 1000;
            $esMsg->dep_id = $item->dep_id;

            // Start of chat based attributes for filtering support
            $esMsg->gbot_id = isset($chatsData[$item->chat_id]->gbot_id) ? $chatsData[$item->chat_id]->gbot_id : 0;
            $esMsg->iwh_id = isset($chatsData[$item->chat_id]->iwh_id) ? $chatsData[$item->chat_id]->iwh_id : 0;
            $esMsg->country_code = isset($chatsData[$item->chat_id]->country_code) ? $chatsData[$item->chat_id]->country_code : '';
            $esMsg->transfer_uid = isset($chatsData[$item->chat_id]->transfer_uid) ? $chatsData[$item->chat_id]->transfer_uid : 0;
            $esMsg->status_sub = isset($chatsData[$item->chat_id]->status_sub) ? $chatsData[$item->chat_id]->status_sub : 0;
            $esMsg->invitation_id = isset($chatsData[$item->chat_id]->invitation_id) ? $chatsData[$item->chat_id]->invitation_id : 0;
            $esMsg->abnd = isset($chatsData[$item->chat_id]) ? (($chatsData[$item->chat_id]->lsync < ($chatsData[$item->chat_id]->pnd_time + $chatsData[$item->chat_id]->wait_time) && $chatsData[$item->chat_id]->wait_time > 1) || ($chatsData[$item->chat_id]->lsync > ($chatsData[$item->chat_id]->pnd_time + $chatsData[$item->chat_id]->wait_time) && $chatsData[$item->chat_id]->wait_time > 1 && $chatsData[$item->chat_id]->user_id == 0) ? 1 : 0) : 0;
            $esMsg->drpd = isset($chatsData[$item->chat_id]) ? ($chatsData[$item->chat_id]->lsync > ($chatsData[$item->chat_id]->pnd_time + $chatsData[$item->chat_id]->wait_time) && $chatsData[$item->chat_id]->has_unread_op_messages == 1 && $chatsData[$item->chat_id]->user_id > 0 ? 1 : 0) : 0;
            $esMsg->subject_id = [];

            $db = ezcDbInstance::get();
            $stmt = $db->prepare("SELECT `subject_id` FROM `lh_abstract_subject_chat` WHERE `chat_id` = :chat_id");
            $stmt->bindValue(':chat_id', $item->chat_id,PDO::PARAM_INT);
            $stmt->execute();
            $subjectIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
            if (!empty($subjectIds)) {
                foreach ($subjectIds as $subjectId) {
                    $esMsg->subject_id[] = (int)$subjectId;
                }
            }
            // End of chat based attributes

            $indexSave = erLhcoreClassModelESParticipant::$indexName . '-' . erLhcoreClassModelESParticipant::$elasticType;

            if (isset($esMsg->meta_data['index']) && $esMsg->meta_data['index'] != '') {
                $indexSave = $esMsg->meta_data['index'];
            } else if (isset($dataOptions['index_type'])) {
                if ($dataOptions['index_type'] == 'daily') {
                    $indexSave = erLhcoreClassModelESParticipant::$indexName . '-' . erLhcoreClassModelESParticipant::$elasticType . '-' . date('Y.m.d', $item->time);
                } elseif ($dataOptions['index_type'] == 'yearly') {
                    $indexSave = erLhcoreClassModelESParticipant::$indexName . '-' . erLhcoreClassModelESParticipant::$elasticType . '-' . date('Y',$item->time);
                } elseif ($dataOptions['index_type'] == 'monthly') {
                    $indexSave = erLhcoreClassModelESParticipant::$indexName . '-' . erLhcoreClassModelESParticipant::$elasticType . '-' . date('Y.m',$item->time);
                }
            }

            $objectsSave[$indexSave][] = $esMsg;
        }

        if (!empty($objectsSave)) {
            erLhcoreClassModelESParticipant::bulkSave($objectsSave, array('custom_index' => true, 'ignore_id' => true));
        }
    }
    
    public static function hasPreviousMessages($params)
    {
        if ($params['has_messages'] === false) {

            $chat = $params['chat'];

            $ignoreFilter = false;

            if (($online_user = $chat->online_user) !== false) {
                $sparams['body']['query']['bool']['must'][]['term']['online_user_id'] = $online_user->id;
            } elseif ($chat->nick != '' && $chat->nick != 'Visitor' && $chat->nick != 'undefined' && (int)erLhcoreClassModelChatConfig::fetch('elasticsearch_options')->data['use_es_prev_chats'] == 1) {
                $sparams['body']['query']['bool']['must'][]['term']['nick_keyword'] = $chat->nick;
            } else {
                $sparams = array();

                erLhcoreClassChatEventDispatcher::getInstance()->dispatch('elasticsearch.getpreviouschats_abstract', array(
                    'chat' => $chat,
                    'sparams' => & $sparams
                ));

                if (empty($sparams)){
                    return array(
                        'status' => erLhcoreClassChatEventDispatcher::STOP_WORKFLOW,
                        'has_messages' => false
                    );
                } else {
                    $ignoreFilter = true;
                }
            }

            if ($ignoreFilter == false) {

                $sparams['body']['query']['bool']['must'][]['range']['chat_id']['lt'] = $chat->id;

                erLhcoreClassChatEventDispatcher::getInstance()->dispatch('elasticsearch.getpreviouschats', array(
                    'chat' => $chat,
                    'sparams' => & $sparams
                ));
            }

            try {
                $previousChat = erLhcoreClassModelESChat::findOne(array(
                    'offset' => 0,
                    'limit' => 1,
                    'body' => array_merge(array(
                        'sort' => array(
                            'time' => array(
                                'order' => 'desc'
                            )
                        )
                    ), $sparams['body'])
                ),
                    array('date_index' => array('gte' => ($chat->time - (31*24*3600)))));
            } catch (Exception $e) {
                error_log($e->getMessage() . "\n" . $e->getTraceAsString());
                return array(
                    'status' => erLhcoreClassChatEventDispatcher::STOP_WORKFLOW,
                    'has_messages' => false
                );
            }

            if ($previousChat instanceof erLhcoreClassModelESChat) {
                return array(
                    'status' => erLhcoreClassChatEventDispatcher::STOP_WORKFLOW,
                    'has_messages' => true,
                    'message_id' => 0,
                    'chat_history' => erLhcoreClassModelChat::fetch($previousChat->chat_id)
                );
            } else {
                return array(
                    'status' => erLhcoreClassChatEventDispatcher::STOP_WORKFLOW,
                    'has_messages' => false
                );
            }
        }
    }

    public static function getConcurrentChats($params)
    {
        $response = array(
            'status' => erLhcoreClassChatEventDispatcher::STOP_WORKFLOW,
        );

        $sparams = array(
            'body' => array()
        );

        $sparams['body']['query']['bool']['must'][]['range']['cls_time']['gt'] = (int)$params['chat']->time * 1000;
        $sparams['body']['query']['bool']['must'][]['range']['chat_id']['lt'] = (int)$params['chat']->id;
        $sparams['body']['query']['bool']['must'][]['term']['user_id'] = (int)$params['chat']->user_id;

        $dateFilter['gte'] = $params['chat']->time + 10;
        $dateFilter['lte'] = $params['chat']->time - 10;

        $sort = array('chat_id' => array('order' => 'desc'));

        $response['previous_chats']  = array_reverse(erLhcoreClassModelESChat::getList(array(
            'offset' => 0,
            'limit' => 10,
            'body' => array_merge(array(
                'sort' => $sort
            ), $sparams['body'])
        ),
        array('date_index' => $dateFilter)));

        /* next chats */

        $sparams = array(
            'body' => array()
        );

        $sparams['body']['query']['bool']['must'][]['range']['time']['lt'] = (int)$params['chat']->cls_time * 1000;
        $sparams['body']['query']['bool']['must'][]['range']['chat_id']['gt'] = (int)$params['chat']->id;
        $sparams['body']['query']['bool']['must'][]['term']['user_id'] = (int)$params['chat']->user_id;

        $sort = array('chat_id' => array('order' => 'asc'));

        $response['next_chats'] = erLhcoreClassModelESChat::getList(array(
            'offset' => 0,
            'limit' => 10,
            'body' => array_merge(array(
                'sort' => $sort
            ), $sparams['body'])
        ),
            array('date_index' => $dateFilter));

        $response['processed'] = true;

        return $response;
    }

    public static function getChatHistory($params)
    {
        $result = self::hasPreviousMessages(array(
                'has_messages' => false,
                'chat' => $params['chat']
            )
        );

        $response = array(
            'status' => erLhcoreClassChatEventDispatcher::STOP_WORKFLOW,
        );

        if ($result['has_messages'] == true) {
            $response['has_messages'] = true;
            $response['chat'] = $result['chat_history'];
        } else {
            $response['has_messages'] = false;
            $response['chat'] = null;
        }

        return $response;
    }

    public static function mailMessageRemove($params) {
        $db = ezcDbInstance::get();
        $stmt = $db->prepare('INSERT IGNORE INTO lhc_lhesmail_index (`mail_id`,`op`,`udate`) VALUES (:mail_id,3,:udate)');
        $stmt->bindValue(':mail_id', $params['message']->id, PDO::PARAM_STR);
        $stmt->bindValue(':udate', $params['message']->udate, PDO::PARAM_STR);
        $stmt->execute();

        // Schedule background worker for instant indexing
        if (class_exists('erLhcoreClassExtensionLhcphpresque')) {
            erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionLhcphpresque')->enqueue('lhc_elastic_queue', 'erLhcoreClassElasticSearchWorker', array());
        }
    }

    public static function mailMessageIndex($params) {
        $db = ezcDbInstance::get();

        try {
            $stmt = $db->prepare('INSERT IGNORE INTO lhc_lhesmail_index (`mail_id`) VALUES (:mail_id)');
            $stmt->bindValue(':mail_id', $params['message']->id, PDO::PARAM_INT);
            $stmt->execute();
        } catch (Exception $e) {
            // Ignore an error if deadlock is found
            // Perhaps we should handle it different way, but usually it happens rarely
            // and mails re re-indexed multiple times during their lifespan
        }

        // Schedule background worker for instant indexing
        if (class_exists('erLhcoreClassExtensionLhcphpresque')) {
            erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionLhcphpresque')->enqueue('lhc_elastic_queue', 'erLhcoreClassElasticSearchWorker', array());
        }
    }

    public static function conversationIndex($params) {
        $db = ezcDbInstance::get();
        try {
            $stmt = $db->prepare('INSERT IGNORE INTO lhc_lhesmail_index (`mail_id`,`op`) VALUES (:mail_id,1)');
            $stmt->bindValue(':mail_id', $params['conversation']->id, PDO::PARAM_INT);
            $stmt->execute();
        } catch (Exception $e) {
            // Ignore an error if deadlock is found
            // Perhaps we should handle it different way, but usually it happens rarely
            // and mails re re-indexed multiple times during their lifespan
        }

        // Schedule background worker for instant indexing
        if (class_exists('erLhcoreClassExtensionLhcphpresque')) {
            erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionLhcphpresque')->enqueue('lhc_elastic_queue', 'erLhcoreClassElasticSearchWorker', array());
        }
    }

    public static function indexMails($params) {
        $sparams = array();
        $sparams['body']['query']['bool']['must'][]['terms']['_id'] = array_keys($params['mails']);
        $sparams['limit'] = 1000;

        $dateRange = array();
        foreach ($params['mails'] as $item) {
            if ($item->udate > 0) {
                $dateRange[] = $item->udate;
            }
        }

        $documentsReindexed = erLhcoreClassModelESMail::getList($sparams,array('date_index' => array('gte' => min($dateRange), 'lte' => max($dateRange))));

        $objectsSave = array();

        $esOptions = erLhcoreClassModelChatConfig::fetch('elasticsearch_options');
        $dataOptions = (array)$esOptions->data;

        if (isset($dataOptions['check_if_exists']) && $dataOptions['check_if_exists'] == 1)
        {
            $dateRangesIndex = [];
            foreach ($dateRange as $dateRangeItem) {
                if ($dataOptions['index_type'] == 'daily') {
                    $dateRangesIndex[] = date('Y.m.d',$dateRangeItem);
                } elseif ($dataOptions['index_type'] == 'yearly') {
                    $dateRangesIndex[] = date('Y',$dateRangeItem);
                } elseif ($dataOptions['index_type'] == 'monthly') {
                    $dateRangesIndex[] = date('Y.m',$dateRangeItem);
                }
            }

            if (!empty($dateRangesIndex)) {
                $settings = include ('extension/elasticsearch/settings/settings.ini.php');

                foreach (array_unique($dateRangesIndex) as $indexPrepend)
                {
                    $sessionElasticStatistic = erLhcoreClassModelESMail::getSession();
                    $esSearchHandler = erLhcoreClassElasticClient::getHandler();
                    erLhcoreClassElasticClient::indexExists($esSearchHandler, $settings['index'], $indexPrepend, true);
                }
            }
        }

        foreach ($params['mails'] as $keyValue => $item) {
            if (isset($documentsReindexed[$keyValue])) {
                $esChat = $documentsReindexed[$keyValue];
            } else {
                $esChat = new erLhcoreClassModelESMail();
            }

            $esChat->id = $item->id;
            $esChat->status = $item->status;
            $esChat->conversation_id = $item->conversation_id;
            $esChat->conversation_id_old = $item->conversation_id_old;
            $esChat->mailbox_id = $item->mailbox_id;
            $esChat->subject = $item->subject;
            $esChat->body = strip_tags($item->body);
            $esChat->alt_body = $item->alt_body;
            $esChat->message_id = $item->message_id;
            $esChat->in_reply_to = $item->in_reply_to;
            $esChat->subject = $item->subject;
            $esChat->references = $item->references;
            $esChat->time = $item->udate * 1000;
            $esChat->ctime = $item->ctime * 1000;
            $esChat->opened_at = $item->opened_at * 1000;
            $esChat->flagged = $item->flagged;
            $esChat->recent = $item->recent;
            $esChat->msgno = $item->msgno;
            $esChat->uid = $item->uid;
            $esChat->size = $item->size;
            $esChat->lang = $item->lang;

            $esChat->from_host = $item->from_host;
            $esChat->from_name = $item->from_name;
            $esChat->from_address = $item->from_address;

            $esChat->sender_host = $item->sender_host;
            $esChat->sender_name = $item->sender_name;
            $esChat->sender_address = $item->sender_address;

            $esChat->to_data = self::makeKeywords(json_decode($item->to_data,true));
            $esChat->reply_to_data = self::makeKeywords(json_decode($item->reply_to_data,true));
            $esChat->cc_data = self::makeKeywords(json_decode($item->cc_data,true));
            $esChat->bcc_data = self::makeKeywords(json_decode($item->bcc_data,true));

            $esChat->response_time = $item->response_time;
            $esChat->cls_time = $item->cls_time * 1000;
            $esChat->wait_time = $item->wait_time;
            $esChat->accept_time = $item->accept_time * 1000;
            $esChat->interaction_time = $item->interaction_time;
            $esChat->lr_time = $item->lr_time * 1000;
            $esChat->conv_duration = $item->conv_duration;
            $esChat->user_id = $item->user_id;
            $esChat->response_type = $item->response_type;
            $esChat->dep_id = $item->dep_id;
            $esChat->mb_folder = $item->mb_folder;
            $esChat->has_attachment = $item->has_attachment;
            $esChat->rfc822_body = mb_substr($item->rfc822_body,0,500);
            $esChat->delivery_status = self::makeKeywords($item->delivery_status_keyed);
            $esChat->undelivered = $item->undelivered;
            $esChat->priority = $item->priority;

            // Conversation attributes
            if ($item->conversation instanceof erLhcoreClassModelMailconvConversation) {
                $esChat->conv_user_id = $item->conversation->user_id;
                $esChat->status_conv = $item->conversation->status;
                $esChat->start_type = $item->conversation->start_type;
                $esChat->mail_variables = $item->conversation->mail_variables;
                $esChat->follow_up_id = $item->conversation->follow_up_id;
                $esChat->phone = $item->conversation->phone;
                $esChat->customer_name = $item->conversation->from_name;
                $esChat->customer_address = $item->conversation->from_address;
            }

            $esChat->subject_id = [];

            $db = ezcDbInstance::get();
            $stmt = $db->prepare("SELECT `subject_id` FROM `lhc_mailconv_msg_subject` WHERE `message_id` = :message_id");
            $stmt->bindValue(':message_id', $item->id,PDO::PARAM_INT);
            $stmt->execute();
            $subjectIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
            if (!empty($subjectIds)) {
                foreach ($subjectIds as $subjectId) {
                    $esChat->subject_id[] = (int)$subjectId;
                }
            }

            // Extensions can append custom value
            erLhcoreClassChatEventDispatcher::getInstance()->dispatch('elasticsearch.indexmail', array(
                'mail' => & $esChat
            ));

            // Store hour as UTC for easier grouping
            $date_utc = new \DateTime('now', new \DateTimeZone("UTC"));
            $date_utc->setTimestamp($item->udate);
            $esChat->hour = $date_utc->format("H");

            $indexSave = erLhcoreClassModelESMail::$indexName . '-' . erLhcoreClassModelESMail::$elasticType;

            if (isset($esChat->meta_data['index']) && $esChat->meta_data['index'] != '') {
                $indexSave = $esChat->meta_data['index'];
            } else if (isset($dataOptions['index_type'])) {
                if ($dataOptions['index_type'] == 'daily') {
                    $indexSave = erLhcoreClassModelESMail::$indexName . '-' .erLhcoreClassModelESMail::$elasticType . '-' . gmdate('Y.m.d',$item->udate);
                } elseif ($dataOptions['index_type'] == 'yearly') {
                    $indexSave = erLhcoreClassModelESMail::$indexName . '-' .erLhcoreClassModelESMail::$elasticType . '-' . gmdate('Y',$item->udate);
                } elseif ($dataOptions['index_type'] == 'monthly') {
                    $indexSave = erLhcoreClassModelESMail::$indexName . '-' .erLhcoreClassModelESMail::$elasticType . '-' . gmdate('Y.m',$item->udate);
                }
            }

            $objectsSave[$indexSave][] = $esChat;
        }

        erLhcoreClassModelESMail::bulkSave($objectsSave, array('custom_index' => true, 'ignore_id' => true));
    }

    public static function makeKeywords($array) {

        if (!is_array($array) || empty($array)){
            return null;
        }

        $pairs = [];

        foreach ($array as $key => $item) {
            $pairs[] = trim((!is_numeric($key) ? $key . ' ' : '').$item);
        }

        $pairs = array_filter($pairs);
        
        return implode(' ',$pairs);

    }
}