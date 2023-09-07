<?php
#[\AllowDynamicProperties]
class erLhcoreClassModelESParticipant
{
    use erLhcoreClassElasticTrait;

    public function getState()
    {
        $states = array(
            'chat_id' => $this->chat_id,
            'user_id' => $this->user_id,
            'duration' => $this->duration,
            'time' => $this->time,
            'dep_id' => $this->dep_id,
            'gbot_id' => $this->gbot_id,
            'iwh_id' => $this->iwh_id,
            'country_code' => $this->country_code,
            'transfer_uid' => $this->transfer_uid,
            'status_sub' => $this->status_sub,
            'invitation_id' => $this->invitation_id,
            'abnd' => $this->abnd,
            'drpd' => $this->drpd,
            'subject_id' => $this->subject_id,
        );

        return $states;
    }

    public function __get($var)
    {
        switch ($var) {

            case 'time_front':
                $this->time_front = date('Ymd') == date('Ymd', $this->time / 1000) ? date(erLhcoreClassModule::$dateHourFormat, $this->time / 1000) : date(erLhcoreClassModule::$dateDateHourFormat, $this->time / 1000);
                return $this->time_front;
                ;
                break;

            case 'duration_front':
                $this->duration_front = erLhcoreClassChat::formatSeconds($this->duration);
                return $this->duration_front;
                ;
                break;

            default:
                break;
        }
    }

    public static $elasticType = 'lh_participant';

    public $chat_id = null;

    public $user_id = null;

    public $duration = null;

    public $time = null;

    public $dep_id = null;
    public $iwh_id = null;
    public $country_code = null;
    public $transfer_uid = null;
    public $status_sub = null;
    public $invitation_id = null;
    public $abnd = null;
    public $drpd = null;
    public $subject_id = null;

}