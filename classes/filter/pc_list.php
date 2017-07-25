<?php

$fieldsSearch = array();

$fieldsSearch['dep_id'] = array (
    'type' => 'text',
    'trans' => 'Sort by',
    'required' => false,
    'valid_if_filled' => false,
    'filter_type' => 'like',
    'filter_table_field' => 'dep_id',
    'validation_definition' => new ezcInputFormDefinitionElement (
            ezcInputFormDefinitionElement::OPTIONAL, 'string'
    )
);

$fieldSortAttr = array (
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