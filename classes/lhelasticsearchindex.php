<?php

class erLhcoreClassElasticSearchIndex
{
    public static $ts = null;

    public static function indexChats($params)
    {
        $sparams = array();
        $sparams['body']['query']['bool']['must'][]['terms']['chat_id'] = array_keys($params['chats']);
        $sparams['limit'] = 1000;
        $documents = erLhcoreClassModelESChat::getList($sparams);
        
        $documentsReindexed = array();
        foreach ($documents as $document) {
            $documentsReindexed[$document->chat_id] = $document;
        }
        
        $objectsSave = array();
        
        $indexSave = '';
        $indexOptions = '';
        $esOptions = erLhcoreClassModelChatConfig::fetch('elasticsearch_options');
        $dataOptions = (array)$esOptions->data;

        foreach ($params['chats'] as $keyValue => $item) {
            if (isset($documentsReindexed[$keyValue])) {
                $esChat = $documentsReindexed[$keyValue];
            } else {
                $esChat = new erLhcoreClassModelESChat();
            }
            
            $esChat->chat_id = $item->id;
            $esChat->time = $item->time * 1000;
            $esChat->pnd_time = $item->pnd_time * 1000;
            $esChat->cls_time = $item->cls_time * 1000;
                        
            if ($item->ip != '') {
                $esChat->ip = $item->ip;
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
            $esChat->usaccept = $item->usaccept;
            $esChat->lsync = $item->lsync;
            $esChat->auto_responder_id = $item->auto_responder_id;

            // Extensions can append custom value
            erLhcoreClassChatEventDispatcher::getInstance()->dispatch('elasticsearch.indexchat', array(
                'chat' => & $esChat
            ));

            // Store hour as UTC for easier grouping
            $date_utc = new \DateTime(null, new \DateTimeZone("UTC"));
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
            
            $esChat->msg_system = trim($esChat->msg_system);
            $esChat->msg_operator = trim($esChat->msg_operator);
            $esChat->msg_visitor = trim($esChat->msg_visitor);

            if (isset($dataOptions['index_type'])) {
                if ($dataOptions['index_type'] == 'static') {
                    $indexSave = erLhcoreClassModelESChat::$indexName;
                } elseif ($indexSave == 'daily') {
                    $indexSave = erLhcoreClassModelESChat::$indexName . date('Y.m.d',$item->time);
                } elseif ($indexSave == 'monthly') {
                    $indexSave = erLhcoreClassModelESChat::$indexName . date('Y.m',$item->time);
                }
            }

            $objectsSave[$indexSave][] = $esChat;
        }
        
        erLhcoreClassModelESChat::bulkSave($objectsSave,array('custom_index' => true));
    }

    public static function indexOs($params)
    {
        if (empty($params['items'])) {
            return;
        }
        
        $sparams = array();
        $sparams['body']['query']['bool']['must'][]['terms']['os_id'] = array_keys($params['items']);
        $sparams['limit'] = 1000;
        $documents = erLhcoreClassModelESOnlineSession::getList($sparams);
        
        $documentsReindexed = array();
        foreach ($documents as $document) {
            $documentsReindexed[$document->os_id] = $document;
        }
        
        $objectsSave = array();
        
        foreach ($params['items'] as $keyValue => $item) {
            if (isset($documentsReindexed[$keyValue])) {
                $osLog = $documentsReindexed[$keyValue];
            } else {
                $osLog = new erLhcoreClassModelESOnlineSession();
                $osLog->user_id = $item->user_id;
                $osLog->os_id = $item->id;
                $osLog->time = $item->time * 1000;
            }
            
            $osLog->lactivity = $item->lactivity * 1000;
            $osLog->duration = $item->duration;
            
            $objectsSave[] = $osLog;
        }
        
        erLhcoreClassModelESOnlineSession::bulkSave($objectsSave);
    }

    public static function indexChatDelay($params)
    {
        $db = ezcDbInstance::get();
        $stmt = $db->prepare('INSERT IGNORE INTO lhc_lheschat_index (`chat_id`) VALUES (:chat_id)');
        $stmt->bindValue(':chat_id', $params['chat']->id, PDO::PARAM_STR);
        $stmt->execute();
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
        ));
        
        if ($chat instanceof erLhcoreClassModelESChat) {
            $chat->removeThis();
        }
    }

    public static function indexPendingChats($params)
    {
        $items = $params['items'];

        $ts = erLhcoreClassElasticSearchIndex::$ts !== null ? erLhcoreClassElasticSearchIndex::$ts*1000 : time()*1000;

        foreach ($items as $keyValue => $item) {
            $esChat = new erLhcoreClassModelESPendingChat();
            $esChat->chat_id = $item->id;
            $esChat->time = $item->time * 1000;
            $esChat->itime = $ts;
            $esChat->dep_id = $item->dep_id;
            $esChat->status = $item->status;

            $objectsSave[] = $esChat;
        }
        
        erLhcoreClassModelESPendingChat::bulkSave($objectsSave);
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

        foreach ($saveObjects as $userId => $data) {
            $opEs = new erLhcoreClassModelESOnlineOperator();
            $opEs->dep_ids = $data['dep_ids'];
            $opEs->user_id = $userId;
            $opEs->itime = $ts;

            $objectsSave[] = $opEs;
        }

        erLhcoreClassModelESOnlineOperator::bulkSave($objectsSave);
    }
    
    public static function indexMessages($params)
    {
        $items = $params['messages'];
        
        $chatsIds = array();
        foreach ($items as $item) {
            $chatsIds[] = $item->chat_id;
        }
        
        if (empty($chatsIds)) {
            return;
        }
        
        $sql = "SELECT id, dep_id, user_id FROM lh_chat WHERE id IN (" . implode(',', $chatsIds) . ')';
        
        $db = ezcDbInstance::get();
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $chatsData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $infoChat = array();
        
        foreach ($chatsData as $chatData) {
            $infoChat[$chatData['id']]['dep_id'] = $chatData['dep_id'];
            $infoChat[$chatData['id']]['user_id'] = $chatData['user_id'];
        }
        
        $sparams = array();
        $sparams['body']['query']['bool']['must'][]['terms']['msg_id'] = array_keys($params['messages']);
        $sparams['limit'] = 1000;
        $documents = erLhcoreClassModelESMsg::getList($sparams);
        
        $documentsReindexed = array();
        foreach ($documents as $document) {
            $documentsReindexed[$document->msg_id] = $document;
        }
        
        $objectsSave = array();
        
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
                $esMsg->dep_id = $infoChat[$item->chat_id]['dep_id'];
                $esMsg->op_user_id = $infoChat[$item->chat_id]['user_id'];

                $objectsSave[] = $esMsg;
            }
        }

        if (!empty($objectsSave)) {
            erLhcoreClassModelESMsg::bulkSave($objectsSave);
        }
    }
}