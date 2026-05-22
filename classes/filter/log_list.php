<?php

$fieldsSearch = array();

$fieldsSearch['chat_id'] = array(
    'type' => 'text',
    'trans' => 'Chat ID',
    'required' => false,
    'valid_if_filled' => false,
    'filter_type' => 'filter',
    'filter_table_field' => 'chat_id',
    'validation_definition' => new ezcInputFormDefinitionElement(
        ezcInputFormDefinitionElement::OPTIONAL, 'int'
    )
);

$fieldSortAttr = array(
    'field'      => false,
    'default'    => false,
    'serialised' => true,
    'disabled'   => true,
    'options'    => array()
);

return array(
    'filterAttributes' => $fieldsSearch,
    'sortAttributes'   => $fieldSortAttr
);
