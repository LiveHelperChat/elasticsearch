<?php

class erLhcoreClassModelESOnlineOperator
{
    use erLhcoreClassElasticTrait;

    public function getState()
    {
        $states = array(
            'user_id' => $this->user_id,
            'dep_id' => $this->dep_id,
            'itime' => $this->itime
        );
        
        return $states;
    }

    public function __get($var)
    {
        switch ($var) {
            
            case 'itime_front':
                $this->itime_front = date('Ymd') == date('Ymd', $this->itime / 1000) ? date(erLhcoreClassModule::$dateHourFormat, $this->itime / 1000) : date(erLhcoreClassModule::$dateDateHourFormat, $this->itime / 1000);
                return $this->itime_front;
                ;
                break;
                        
            default:
                break;
        }
    }

    public static $elasticType = 'lh_op';

    public $itime = null;

    public $user_id = null;

    public $dep_id = null;
}