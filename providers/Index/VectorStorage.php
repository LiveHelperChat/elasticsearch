<?php

namespace LiveHelperChatExtension\elasticsearch\providers\Index;

#[\AllowDynamicProperties]
class VectorStorage
{
    use \erLhcoreClassElasticTrait;

    public function getState()
    {
        return array(
            'id' => $this->id,
            'name' => $this->name,
            'dep_id' => $this->dep_id,
            'content' => $this->content,
            'vector_storage' => $this->vector_storage,
            'created_at' => $this->created_at,
            'parent_id' => $this->parent_id,
            'original_content' => $this->original_content
        );
    }

    public static $elasticType = 'lh_vector_storage';

    public static $index = '';

    public $id = null;
    public $name = '';
    public $dep_id = 0;
    public $content = '';
    public $vector_storage = null;
    public $created_at = null;
    public $parent_id = '0';
    public $original_content = '';
}
