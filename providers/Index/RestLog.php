<?php

namespace LiveHelperChatExtension\elasticsearch\providers\Index;

class RestLog
{
    use \erLhcoreClassElasticTrait;

    public function __set($name, $value)
    {
        if ($name === '@timestamp') {
            $this->timestamp = $value;
            return;
        }

        $this->$name = $value;
    }

    public function __get($name)
    {
        if ($name === '@timestamp') {
            return $this->timestamp;
        }

        return null;
    }

    public function getState()
    {
        return array(
            'id' => $this->id,
            'chat_id' => $this->chat_id,
            'time' => $this->time,
            'msg' => $this->msg,
            'meta_msg' => $this->meta_msg,
            '@timestamp' => $this->timestamp !== null ? $this->timestamp : $this->time
        );
    }

    public static $elasticType = 'lh_rest_log';

    public $id = null;
    public $chat_id = null;
    public $time = null;
    public $msg = '';
    public $meta_msg = '';
    public $meta_data = [];
    public $timestamp = null;
}