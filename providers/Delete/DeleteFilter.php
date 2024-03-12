<?php

namespace LiveHelperChatExtension\elasticsearch\providers\Delete;

class DeleteFilter
{
    use \erLhcoreClassDBTrait;

    public static $dbTable = 'lhc_mailconv_delete_filter_elastic';

    public static $dbTableId = 'id';

    public static $dbSessionHandler = '\erLhcoreClassExtensionElasticsearch::getSession';

    public static $dbSortOrder = 'DESC';

    public static $dbDefaultSort = 'id DESC';

    public function getState()
    {
        $stateArray = array(
            'id' => $this->id,
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
            'filter' => $this->filter,
            'filter_input' => $this->filter_input,
            'user_id' => $this->user_id,
            'archive_id' => $this->archive_id,
            'status' => $this->status,
            'last_id' => $this->last_id,
            'started_at' => $this->started_at,
            'finished_at' => $this->finished_at,
            'processed_records' => $this->processed_records,
        );

        return $stateArray;
    }

    public function beforeSave()
    {
        $this->updated_at = $this->created_at = time();
    }

    public function beforeUpdate()
    {
        $this->updated_at = time();
    }

    public function __get( $propertyName )
    {
        if ( array_key_exists( $propertyName, $this->properties ) )
        {
            return $this->properties[$propertyName];
        }

        switch ($propertyName) {
            case 'created_at_front':
            case 'updated_at_front':
            case 'started_at_front':
            case 'finished_at_front':
                $var = str_replace('_front','',$propertyName);
                if ($this->{$var} > 0) {
                    $this->properties[$propertyName] = date('Ymd') == date('Ymd',$this->{$var}) ? date(\erLhcoreClassModule::$dateHourFormat,$this->{$var}) : date(\erLhcoreClassModule::$dateDateHourFormat,$this->{$var});
                } else {
                    $this->properties[$propertyName] = '-';
                }
                return $this->properties[$propertyName];

            case 'records_count':
                $this->properties[$propertyName] = DeleteItem::getCount(['filter' => ['filter_id' => $this->id]]);
                return $this->properties[$propertyName];

            case 'records_count_progress':
                $this->properties[$propertyName] = DeleteItem::getCount(['filter' => ['filter_id' => $this->id, 'status' => DeleteItem::STATUS_IN_PROGRESS]]);
                return $this->properties[$propertyName];

            case 'filter_input_url':
                $filter = json_decode($this->filter_input,true);
                $filterParams = \erLhcoreClassSearchHandler::getParams(array('customfilterfile' => 'extension/elasticsearch/classes/filter/mail_list.php', 'format_filter' => true, 'uparams' => $filter));
                $this->properties[$propertyName] = \erLhcoreClassDesign::baseurl('elasticsearch/listmail') .  \erLhcoreClassSearchHandler::getURLAppendFromInput($filterParams['input_form']);
                return $this->properties[$propertyName];
            default:
                break;
        }
    }

    protected $properties = [];

    const STATUS_PENDING = 0;
    const STATUS_IN_PROGRESS = 1;
    const STATUS_FINISHED = 2;

    public $id = null;
    public $updated_at = null;
    public $created_at = null;
    public $filter = null;
    public $filter_input = null;
    public $user_id = null;
    public $last_id = 0;
    public $archive_id = 0;
    public $finished_at = 0;
    public $started_at = 0;
    public $processed_records = 0;
    public $status = self::STATUS_PENDING;
}

?>