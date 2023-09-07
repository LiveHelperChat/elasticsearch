<?php
#[\AllowDynamicProperties]
class erLhcoreClassModelESMail
{
    use erLhcoreClassElasticTrait;

    public function getState()
    {
        $states = array(
            'id' => $this->id,
            'status' => $this->status,
            'conversation_id' => $this->conversation_id,
            'conversation_id_old' => $this->conversation_id_old,
            'message_id' => $this->message_id,
            'in_reply_to'  => $this->in_reply_to,
            'subject' => $this->subject,
            'body' => $this->body,
            'alt_body' => $this->alt_body,
            'references' => $this->references,
            'ctime' => $this->ctime,
            'time' => $this->time,
            'flagged' => $this->flagged,
            'recent' => $this->recent,
            'msgno' => $this->msgno,
            'uid' => $this->uid,
            'size' => $this->size,
            'from_host' => $this->from_host,
            'from_name' => $this->from_name,
            'from_address' => $this->from_address,
            'sender_host' => $this->sender_host,
            'sender_name' => $this->sender_name,
            'sender_address' => $this->sender_address,
            'to_data' => $this->to_data,
            'reply_to_data' => $this->reply_to_data,
            'mailbox_id' => $this->mailbox_id,
            'response_time' => $this->response_time,
            'cls_time' => $this->cls_time,
            'wait_time' => $this->wait_time,
            'accept_time' => $this->accept_time,
            'interaction_time' => $this->interaction_time,
            'user_id' => $this->user_id,
            'conv_user_id' => $this->conv_user_id,
            'lr_time' => $this->lr_time,
            'response_type' => $this->response_type,
            'bcc_data' => $this->bcc_data,
            'cc_data' => $this->cc_data,
            'dep_id' => $this->dep_id,
            'mb_folder' => $this->mb_folder,
            'conv_duration' => $this->conv_duration,
            'has_attachment' => $this->has_attachment,
            'rfc822_body' => $this->rfc822_body,
            'delivery_status' => $this->delivery_status,
            'undelivered' => $this->undelivered,
            'subject_id' => $this->subject_id,
            'hour' => $this->hour,
            'priority' => $this->priority,
            'status_conv' => $this->status_conv,
            'start_type' => $this->start_type,
            'mail_variables' => $this->mail_variables,
            'follow_up_id' => $this->follow_up_id,
            'lang' => $this->lang,
            'opened_at' => $this->opened_at,
            'phone' => $this->phone,
            'customer_address' => $this->customer_address,
            'customer_name' => $this->customer_name,
        );

        erLhcoreClassChatEventDispatcher::getInstance()->dispatch('elasticsearch.getstate_mail', array(
            'state' => & $states,
            'mail' => & $this
        ));

        return $states;
    }

    public function __get($var)
    {
        switch ($var) {

            case 'department':
                $this->department = false;
                if ($this->dep_id > 0) {
                    try {
                        $this->department = erLhcoreClassModelDepartament::fetch($this->dep_id,true);
                    } catch (Exception $e) {

                    }
                }
                return $this->department;

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

            case 'conv_user':
                $this->conv_user = false;
                if ($this->conv_user_id > 0) {
                    try {
                        $this->conv_user = erLhcoreClassModelUser::fetch($this->conv_user_id,true);
                    } catch (Exception $e) {
                        $this->conv_user = false;
                    }
                }
                return $this->conv_user;

            case 'mailbox':
                $this->mailbox = erLhcoreClassModelMailconvMailbox::fetch($this->mailbox_id);
                return $this->mailbox;

            case 'subject_front':
                $this->subject_front = $this->subject != '' ? $this->subject : ($this->from_name != '' ? $this->from_name : $this->id.' '.$this->from_address);
                return $this->subject_front;

            case 'mailbox_front':
                $this->mailbox_front = [
                    'name' => '',
                    'mail' => '',
                ];

                if ($this->mailbox instanceof erLhcoreClassModelMailconvMailbox) {
                    $this->mailbox_front['name'] = $this->mailbox->name;
                    $this->mailbox_front['mail'] = $this->mailbox->mail;
                }

                return $this->mailbox_front;

            case 'last_mail_front':
                $this->last_mail_front = erLhcoreClassChat::formatSeconds(time() - $this->time/1000);
                return $this->last_mail_front;

            case 'subjects':
                $this->subjects = [];
                if (is_array($this->subject_id) && !empty($this->subject_id)) {
                    $this->subjects = erLhAbstractModelSubject::getList(['filterin' => ['id' => $this->subject_id]]);
                }
                return $this->subjects;

            case 'mail_variables_array':
                if (!empty($this->mail_variables)) {
                    $jsonData = json_decode($this->mail_variables,true);
                    if ($jsonData !== null) {
                        $this->mail_variables_array = $jsonData;
                    } else {
                        $this->mail_variables_array = $this->mail_variables;
                    }
                } else {
                    $this->mail_variables_array = array();
                }
                return $this->mail_variables_array;

            default:
                break;
        }
    }

    public static $elasticType = 'lh_mail';

    public $id = null;
    public $status = null;
    public $conversation_id = null;
    public $message_id = null;
    public $in_reply_to = null;
    public $subject = null;
    public $body = null;
    public $alt_body = null;
    public $references = null;
    public $ctime = null;
    public $time = null;
    public $flagged = null;
    public $recent = null;
    public $msgno = null;
    public $uid = null;
    public $size = null;
    public $from_host = null;
    public $from_name = null;
    public $from_address = null;
    public $sender_host = null;
    public $sender_name = null;
    public $sender_address = null;
    public $to_data = null;
    public $reply_to_data = null;
    public $mailbox_id = null;
    public $response_time = null;
    public $cls_time = null;
    public $opened_at = null;
    public $wait_time = null;
    public $accept_time = null;
    public $interaction_time = null;
    public $user_id = null;
    public $lr_time = null;
    public $response_type = null;
    public $bcc_data = null;
    public $cc_data = null;
    public $dep_id = null;
    public $mb_folder = null;
    public $conv_duration = null;
    public $has_attachment = null;
    public $rfc822_body = null;
    public $delivery_status = null;
    public $undelivered = null;
    public $priority = null;
    public $conv_user_id = null;
    public $status_conv = null;
    public $start_type = null;
    public $mail_variables = null;
    public $follow_up_id = null;
    public $lang = null;
    public $phone = null;
    public $customer_address = null;
    public $customer_name = null;
    public $conversation_id_old = null;

    // Dynamic attributes
    public $subject_id = null;
    public $hour = null;
}
