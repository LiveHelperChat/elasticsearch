<?php

namespace LiveHelperChatExtension\elasticsearch\providers\Delete;
class DeleteItem
{
    use \erLhcoreClassDBTrait;

    public static $dbTable = 'lhc_mailconv_delete_item_elastic';

    public static $dbTableId = 'id';

    public static $dbSessionHandler = 'erLhcoreClassExtensionElasticsearch::getSession';

    public static $dbSortOrder = 'DESC';

    public static $dbDefaultSort = 'id DESC';

    public function getState()
    {
        $stateArray = array(
            'id' => $this->id,
            'conversation_id' => $this->conversation_id,
            'filter_id' => $this->filter_id,
            'status' => $this->status,
            'index' => $this->index,
        );

        return $stateArray;
    }

    const STATUS_PENDING = 0;
    const STATUS_IN_PROGRESS = 1;

    public $id = null;
    public $conversation_id = null;
    public $status = self::STATUS_PENDING;
    public $filter_id = null;
    public $index = null;
}

?>