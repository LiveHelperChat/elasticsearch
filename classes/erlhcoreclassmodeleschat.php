<?php
#[\AllowDynamicProperties]
class erLhcoreClassModelESChat
{
    use erLhcoreClassElasticTrait;

    public function getState()
    {
        $states = array(
            'id' => $this->id,
            'user_id' => $this->user_id,
            'transfer_uid' => $this->transfer_uid,
            'chat_id' => $this->chat_id,
            'time' => $this->time,
            'ip'  => $this->ip,
            'location' => $this->location,
            'city' => $this->city,
            'region' => $this->region,
            'wait_time' => $this->wait_time,
            'nick' => $this->nick,
            'nick_keyword' => $this->nick_keyword,
            'status' => $this->status,
            'hash' => $this->hash,
            'referrer' => $this->referrer,
            'dep_id' => $this->dep_id,
            'user_status' => $this->user_status,
            'support_informed' => $this->support_informed,
            'email' => $this->email,
            'country_code' => $this->country_code,
            'country_name' => $this->country_name,
            'phone' => $this->phone,
            'additional_data' => $this->additional_data,
            'mail_send' => $this->mail_send,
            'session_referrer' => $this->session_referrer,
            'chat_duration' => $this->chat_duration,
            'chat_variables' => $this->chat_variables,
            'priority' => $this->priority,
            'online_user_id' => $this->online_user_id,
            'transfer_timeout_ts' => $this->transfer_timeout_ts,
            'transfer_timeout_ac' => $this->transfer_timeout_ac,
            'transfer_if_na' => $this->transfer_if_na,
            'na_cb_executed' => $this->na_cb_executed,
            'fbst' => $this->fbst,
            'nc_cb_executed' => $this->nc_cb_executed,
            'operator_typing_id' => $this->operator_typing_id,
            'remarks' => $this->remarks,
            'status_sub' => $this->status_sub,
            'operation' => $this->operation,
            'screenshot_id' => $this->screenshot_id,
            'operation_admin' => $this->operation_admin,
            'unread_messages_informed' => $this->unread_messages_informed,
            'reinform_timeout' => $this->reinform_timeout,
            'tslasign' => $this->tslasign,
            'user_tz_identifier' => $this->user_tz_identifier,
            'user_closed_ts' => $this->user_closed_ts,
            'chat_locale' => $this->chat_locale,
            'chat_locale_to' => $this->chat_locale_to,
            'unanswered_chat' => $this->unanswered_chat,
            'product_id' => $this->product_id,
            'last_op_msg_time' => $this->last_op_msg_time,
            'has_unread_op_messages' => $this->has_unread_op_messages,
            'has_unread_messages' => $this->has_unread_messages,
            'unread_op_messages_informed' => $this->unread_op_messages_informed,
            'status_sub_sub' => $this->status_sub_sub,
            'status_sub_arg' => $this->status_sub_arg,
            'uagent' => $this->uagent,
            'device_type' => $this->device_type,
            'sender_user_id' => $this->sender_user_id,
            'usaccept' => $this->usaccept,
            'lsync' => $this->lsync,
            'auto_responder_id' => $this->auto_responder_id,
            'chat_initiator' => $this->chat_initiator,
            'hour' => $this->hour,
            'msg_visitor' => $this->msg_visitor,
            'msg_operator' => $this->msg_operator,
            'msg_system' => $this->msg_system,
            'pnd_time' => $this->pnd_time,
            'cls_time' => $this->cls_time,
            'invitation_id' => $this->invitation_id,
            'hof' => $this->hof,
            'hvf' => $this->hvf,
            'gbot_id' => $this->gbot_id,
            'subject_id' => $this->subject_id,
            'abnd' => $this->abnd,
            'drpd' => $this->drpd,
            'cls_us' => $this->cls_us,
            'iwh_id' => $this->iwh_id,
        );

        erLhcoreClassChatEventDispatcher::getInstance()->dispatch('elasticsearch.getstate', array(
            'state' => & $states,
            'chat' => & $this
        ));

        foreach ($this->customGetAttributes as $attrCustom) {
            $states[$attrCustom] = $this->{$attrCustom};
        }

        return $states;
    }

    public function setCustomGetAttributes($attr) {
        $this->customGetAttributes = $attr;
    }

    /**
     * Remove messages
     */
    public function beforeRemove()    
    {
        $sparams['body']['query']['bool']['must'][]['term']['chat_id'] = $this->chat_id;

        $dateFilter['gte'] = round($this->time/1000);

        $items = erLhcoreClassModelESMsg::getList(array(
            'offset' => 0,
            'limit' => 10000,
            'body' => $sparams['body']
        ), array('date_index' => $dateFilter));

        foreach ($items as $item) {
            $item->removeThis();
        }
    }
    
    public function __get($var)
    {
        switch ($var) {

            case 'iwh':
                $this->iwh = $this->iwh_id > 0 ? erLhcoreClassModelChatIncomingWebhook::fetch($this->iwh_id) : null;
                return $this->iwh;

            case 'aicons':
                $this->aicons = [];
                $chatVariables = $this->chat_variables_array;
                if (isset($chatVariables['aicons']) ) {
                    foreach ($chatVariables['aicons'] as $icon => $params) {
                        $iconParams = ['i' => $icon];
                        if (isset($params['icolor'])) {
                            $iconParams['c'] = $params['icolor'];
                        }
                        if (isset($params['t']) && $params['t'] != '') {
                            $iconParams['t'] = $params['t'];
                        }
                        $this->aicons[$icon] = $iconParams;
                    }
                }
                if ($this->iwh_id > 0 && is_object($this->iwh) && $this->iwh->icon != '') {
                    $iconParams = ['i' => $this->iwh->icon];
                    if ($this->iwh->icon_color != '') {
                        $iconParams['c'] = $this->iwh->icon_color ;
                    }
                    $iconParams['t'] = (string)$this->iwh;
                    $this->aicons[$this->iwh->icon] = $iconParams;
                }
                return $this->aicons;

            case 'department':
                $this->department = false;
                if ($this->dep_id > 0) {
                    try {
                        $this->department = erLhcoreClassModelDepartament::fetch($this->dep_id,true);
                    } catch (Exception $e) {

                    }
                }
                return $this->department;

            case 'chat_variables_array':
                if (!empty($this->chat_variables)){
                    $jsonData = json_decode($this->chat_variables,true);
                    if ($jsonData !== null) {
                        $this->chat_variables_array = $jsonData;
                    } else {
                        $chat_variables_array = @unserialize($this->chat_variables);
                        if ($chat_variables_array !== false) {
                            $this->chat_variables_array = $chat_variables_array;
                        } else {
                            $this->chat_variables_array = $this->chat_variables;
                        }
                    }
                } else {
                    $this->chat_variables_array = array();
                }
                return $this->chat_variables_array;

            case 'user':
                $this->user = false;
                if ($this->user_id > 0) {
                    try {
                        $this->user = erLhcoreClassModelUser::fetch($this->user_id,true);
                    } catch (Exception $e) {
                        $this->user = false;
                    }
                }
                return $this->user;

            case 'subjects':
                $this->subjects = [];
                if (is_array($this->subject_id) && !empty($this->subject_id)) {
                    $this->subjects = erLhAbstractModelSubject::getList(['filterin' => ['id' => $this->subject_id]]);
                }
                return $this->subjects;

            default:
                break;
        }
    }

    public static $elasticType = 'lh_chat';

    public $customGetAttributes = [];

    public $id = null;
    public $transfer_uid = null;
    public $nc_cb_executed = null;
    public $operation = null;
    public $status_sub = null;
    public $chat_duration = null;
    public $user_id = null;
    public $online_user_id = null;
    public $priority = null;
    public $email = null;
    public $chat_id = null;
    public $city = null;
    public $region = null;
    public $location = null;
    public $time = null;
    public $referrer = null;
    public $ip = null;    
    public $status = null;    
    public $hash = null;    
    public $nick = null;    
    public $nick_keyword = null;
    public $dep_id = null;
    public $user_status = null;    
    public $country_code = null;    
    public $country_name = null;    
    public $support_informed = null;    
    public $phone = null;
    public $additional_data = null;
    public $mail_send = null;
    public $session_referrer = null;
    public $chat_variables = null;
    public $transfer_timeout_ts = null;
    public $na_cb_executed = null;
    public $fbst = null;
    public $remarks = null;        
    public $operator_typing_id = null;
    public $screenshot_id = null;
    public $operation_admin = null;
    public $unread_messages_informed = null;
    public $reinform_timeout = null;
    public $tslasign = null;
    public $user_tz_identifier = null;
    public $user_closed_ts = null;
    public $chat_locale = null;
    public $chat_locale_to = null;
    public $unanswered_chat = null;
    public $product_id = null;
    public $last_op_msg_time = null;
    public $has_unread_op_messages = null;
    public $has_unread_messages = null;
    public $unread_op_messages_informed = null;
    public $status_sub_sub = null;
    public $status_sub_arg = null;
    public $uagent = null;
    public $device_type = null;
    public $sender_user_id = null;
    public $auto_responder_id = null;
    public $usaccept = null;
    public $lsync = null;
    public $hour = null;
    public $chat_initiator = null;
    public $msg_visitor = null;
    public $msg_operator = null;
    public $msg_system = null;
    public $pnd_time = null;
    public $cls_time = null;
    public $invitation_id = null;
    public $hof = null;
    public $hvf = null;
    public $gbot_id = null;
    public $subject_id = null;
    public $abnd = null;
    public $drpd = null;
    public $cls_us = null;
    public $iwh_id = null;
}
