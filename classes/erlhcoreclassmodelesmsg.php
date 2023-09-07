<?php
#[\AllowDynamicProperties]
class erLhcoreClassModelESMsg
{
    use erLhcoreClassElasticTrait;

    public function getState()
    {
        $states = array(
            'msg_id' => $this->msg_id,
            'msg' => $this->msg,
            'time' => $this->time,
            'chat_id' => $this->chat_id,
            'user_id' => $this->user_id,
            'op_user_id' => $this->op_user_id,
            'name_support' => $this->name_support,
            'dep_id' => $this->dep_id,
            'gbot_id' => $this->gbot_id,
            'status_sub' => $this->status_sub,
            'del_st' => $this->del_st
        );
        
        return $states;
    }

    public function __get($var)
    {
        switch ($var) {
            
            case 'demo':
                $this->demo = '';
                return $this->demo;
                break;
            default:
                break;
        }
    }

    public static $elasticType = 'lh_msg';

    public $msg_id = null;

    public $msg = null;

    public $time = null;

    public $chat_id = null;

    public $user_id = null;

    public $dep_id = null;
    public $del_st = null;

    public $op_user_id = null;

    public $gbot_id = null;

    public $status_sub = null;

    public $name_support = null;
}