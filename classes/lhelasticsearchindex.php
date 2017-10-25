<?php

class erLhcoreClassElasticSearchIndex
{
    public static $ts = null;

    public static function indexChat($params)
    {
        $sparams['body']['query']['bool']['must'][]['term']['chat_id'] = $params['chat']->id;
        
        $chat = erLhcoreClassModelESChat::findOne(array(
            'offset' => 0,
            'limit' => 0,
            'body' => $sparams['body']
        ));
        
        $new = false;
        if ($chat == false) {
            $chat = new erLhcoreClassModelESChat();
            $new = true;
        }
        
        $chat->chat_id = $params['chat']->id;
        $chat->time = $params['chat']->time * 1000;
        
        if ($params['chat']->ip != '') {
            $chat->ip = $params['chat']->ip;
        }
        
        $chat->user_id = $params['chat']->user_id;
        
        if (! empty($params['chat']->lon) && ! empty($params['chat']->lat)) {
            $chat->location = array(
                (float) $params['chat']->lon,
                (float) $params['chat']->lat
            );
        }
        
        $chat->transfer_uid = $params['chat']->transfer_uid;
        $chat->dep_id = $params['chat']->dep_id;
        $chat->city = $params['chat']->city;
        $chat->wait_time = $params['chat']->wait_time;
        $chat->nick = $params['chat']->nick;
        $chat->nick_keyword = $params['chat']->nick;
        $chat->status = $params['chat']->status;
        $chat->hash = $params['chat']->hash;
        $chat->referrer = $params['chat']->referrer;
        $chat->user_status = $params['chat']->user_status;
        $chat->support_informed = $params['chat']->support_informed;
        $chat->email = $params['chat']->email;
        $chat->country_code = $params['chat']->country_code;
        $chat->country_name = $params['chat']->country_name;
        $chat->phone = $params['chat']->phone;
        $chat->has_unread_messages = $params['chat']->has_unread_messages;
        $chat->last_user_msg_time = $params['chat']->last_user_msg_time;
        $chat->last_msg_id = $params['chat']->last_msg_id;
        $chat->additional_data = $params['chat']->additional_data;
        $chat->mail_send = $params['chat']->mail_send;
        $chat->session_referrer = $params['chat']->session_referrer;
        $chat->chat_duration = $params['chat']->chat_duration;
        $chat->chat_variables = $params['chat']->chat_variables;
        $chat->priority = $params['chat']->priority;
        $chat->chat_initiator = $params['chat']->chat_initiator;
        $chat->online_user_id = $params['chat']->online_user_id;
        $chat->transfer_timeout_ts = $params['chat']->transfer_timeout_ts;
        $chat->transfer_timeout_ac = $params['chat']->transfer_timeout_ac;
        $chat->transfer_if_na = $params['chat']->transfer_if_na;
        $chat->na_cb_executed = $params['chat']->na_cb_executed;
        $chat->fbst = $params['chat']->fbst;
        $chat->nc_cb_executed = $params['chat']->nc_cb_executed;
        $chat->operator_typing_id = $params['chat']->operator_typing_id;
        $chat->remarks = $params['chat']->remarks;
        $chat->status_sub = $params['chat']->status_sub;
        $chat->operation = $params['chat']->operation;
        $chat->screenshot_id = $params['chat']->screenshot_id;
        $chat->unread_messages_informed = $params['chat']->unread_messages_informed;
        $chat->reinform_timeout = $params['chat']->reinform_timeout;
        $chat->has_unread_op_messages = $params['chat']->has_unread_op_messages;
        $chat->user_closed_ts = $params['chat']->user_closed_ts;
        $chat->chat_locale = $params['chat']->chat_locale;
        $chat->chat_locale_to = $params['chat']->chat_locale_to;
        $chat->unanswered_chat = $params['chat']->unanswered_chat;
        $chat->product_id = $params['chat']->product_id;
        $chat->last_op_msg_time = $params['chat']->last_op_msg_time;
        $chat->unread_op_messages_informed = $params['chat']->unread_op_messages_informed;
        $chat->status_sub_sub = $params['chat']->status_sub_sub;
        $chat->status_sub_arg = $params['chat']->status_sub_arg;
        $chat->uagent = $params['chat']->uagent;
        $chat->device_type = $params['chat']->device_type;
        $chat->sender_user_id = $params['chat']->sender_user_id;
        $chat->user_tz_identifier = $params['chat']->user_tz_identifier;
        $chat->operation_admin = $params['chat']->operation_admin;
        $chat->tslasign = $params['chat']->tslasign;
        $chat->usaccept = $params['chat']->usaccept;
        $chat->lsync = $params['chat']->lsync;
        $chat->auto_responder_id = $params['chat']->auto_responder_id;

        // Extensions can append custom value
        erLhcoreClassChatEventDispatcher::getInstance()->dispatch('elasticsearch.indexchat', array(
            'chat' => & $chat
        ));

        // Store hour as UTC for easier grouping
        $date_utc = new \DateTime(null, new \DateTimeZone("UTC"));
        $date_utc->setTimestamp($params['chat']->time);
        $chat->hour = $date_utc->format("G");
        
        if ($new == false) {
            $chat->updateThis();
        } else {
            $chat->saveThis();
        }
        
        // Store messages in elastic
        $msgs = erLhcoreClassModelmsg::getList(array(
            'limit' => 5000,
            'filter' => array(
                'chat_id' => $params['chat']->id
            )
        ));
        
        $sparams = array();
        $sparams['body']['query']['bool']['must'][]['term']['chat_id'] = $params['chat']->id;
        
        $esMsgs = erLhcoreClassModelESMsg::getList(array(
            'limit' => 2000,
            'body' => $sparams['body']
        ));
        
        $remapped = array();
        foreach ($esMsgs as $esMsg) {
            $remapped[$esMsg->msg_id] = $esMsg;
        }
        
        foreach ($msgs as $msg) {
            $esMsg = isset($remapped[$msg->id]) ? $remapped[$msg->id] : new erLhcoreClassModelESMsg();
            $esMsg->chat_id = $msg->chat_id;
            $esMsg->msg_id = $msg->id;
            $esMsg->msg = $msg->msg;
            $esMsg->time = $msg->time * 1000;
            $esMsg->name_support = $msg->name_support;
            $esMsg->user_id = $msg->user_id;
            $esMsg->dep_id = $params['chat']->dep_id;
            $esMsg->saveThis();
        }
    }

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
        
        foreach ($params['chats'] as $keyValue => $item) {
            if (isset($documentsReindexed[$keyValue])) {
                $esChat = $documentsReindexed[$keyValue];
            } else {
                $esChat = new erLhcoreClassModelESChat();
            }
            
            $esChat->chat_id = $item->id;
            $esChat->time = $item->time * 1000;
            
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
            
            $objectsSave[] = $esChat;
        }
        
        erLhcoreClassModelESChat::bulkSave($objectsSave);
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