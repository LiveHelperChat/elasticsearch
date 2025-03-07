<?php
#[\AllowDynamicProperties]
class erLhcoreClassModelESPendingChat
{
    use erLhcoreClassElasticTrait;
    
    public function getState()
    {
        $states = array(
            'chat_id' => $this->chat_id,
            'time' => $this->time,
            'itime' => $this->itime,
            'dep_id' => $this->dep_id,
            'status' => $this->status
        );
        
        return $states;
    }

    public function __get($var)
    {
        switch ($var) {

            case 'itime_front':
                $this->itime_front = date('Ymd') == date('Ymd', $this->itime / 1000) ? date(erLhcoreClassModule::$dateHourFormat, $this->itime / 1000) : date(erLhcoreClassModule::$dateDateHourFormat, $this->itime / 1000);
                return $this->itime_front;

            case 'time_front':
                $this->time_front = date('Ymd') == date('Ymd', $this->time / 1000) ? date(erLhcoreClassModule::$dateHourFormat, $this->time / 1000) : date(erLhcoreClassModule::$dateDateHourFormat, $this->time / 1000);
                return $this->time_front;

            case 'department':
                $this->department = false;
                if ($this->dep_id > 0) {
                    try {
                        $this->department = erLhcoreClassModelDepartament::fetch($this->dep_id,true);
                    } catch (Exception $e) {

                    }
                }
                return $this->department;

            case 'chat':
                $this->chat = false;
                if ($this->chat_id > 0) {
                    try {
                        $this->chat = erLhcoreClassModelChat::fetch($this->chat_id,true);
                    } catch (Exception $e) {

                    }
                }
                return $this->chat;

            default:
                break;
        }
    }

    public static $elasticType = 'lh_pc';

    public $chat_id = null;

    public $time = null;

    public $itime = null;
    
    public $status = null;

    public $dep_id = null;   
}