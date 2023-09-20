<?php
#[\AllowDynamicProperties]
class erLhcoreClassModelESOnlineSession
{
    use erLhcoreClassElasticTrait;

    public function getState()
    {
        $states = array(
            'os_id' => $this->os_id,
            'user_id' => $this->user_id,
            'time' => $this->time,
            'lactivity' => $this->lactivity,
            'duration' => $this->duration
        );
        
        return $states;
    }

    public function __get($var)
    {
        switch ($var) {
            
            case 'time_front':
                $this->time_front = date('Ymd') == date('Ymd', $this->time / 1000) ? date(erLhcoreClassModule::$dateHourFormat, $this->time / 1000) : date(erLhcoreClassModule::$dateDateHourFormat, $this->time / 1000);
                return $this->time_front;

            case 'lactivity_front':
                $this->lactivity_front = date('Ymd') == date('Ymd', $this->lactivity / 1000) ? date(erLhcoreClassModule::$dateHourFormat, $this->lactivity / 1000) : date(erLhcoreClassModule::$dateDateHourFormat, $this->lactivity / 1000);
                return $this->lactivity_front;

            case 'duration_front':
                $this->duration_front = erLhcoreClassChat::formatSeconds($this->duration);
                return $this->duration_front;

            default:
                break;
        }
    }

    public static $elasticType = 'lh_os';

    public $os_id = null;

    public $user_id = null;

    public $time = null;

    public $lactivity = null;

    public $duration = null;
}