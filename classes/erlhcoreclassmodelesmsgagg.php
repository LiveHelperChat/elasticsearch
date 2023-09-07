<?php
#[\AllowDynamicProperties]
class erLhcoreClassModelESMsgAgg
{
    use erLhcoreClassElasticTrait;
    
    public function getState()
    {
        $states = array(
            'key' => $this->key,
            'doc_count' => $this->doc_count
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

    public $key = null;

    public $doc_count = null;
    
    public $aggregationName = null;
}
