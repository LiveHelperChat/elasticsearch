<?php

$tpl = erLhcoreClassTemplate::getInstance('elasticsearch/options.tpl.php');

$esOptions = erLhcoreClassModelChatConfig::fetch('elasticsearch_options');
$data = (array)$esOptions->data;

if ( isset($_POST['StoreOptions']) ) {

    $definition = array(
        'use_es_statistic' => new ezcInputFormDefinitionElement(
            ezcInputFormDefinitionElement::OPTIONAL, 'boolean'
        ),
        'use_es_prev_chats' => new ezcInputFormDefinitionElement(
            ezcInputFormDefinitionElement::OPTIONAL, 'boolean'
        ),
        'last_index_msg_id' => new ezcInputFormDefinitionElement(
            ezcInputFormDefinitionElement::OPTIONAL, 'int'
        ),
        'use_es_prev_chats_id' => new ezcInputFormDefinitionElement(
            ezcInputFormDefinitionElement::OPTIONAL, 'boolean'
        ),
        'indexType' => new ezcInputFormDefinitionElement(
            ezcInputFormDefinitionElement::OPTIONAL, 'string'
        )
    );
      
    $form = new ezcInputForm( INPUT_POST, $definition );
    $Errors = array();
            
    if ( $form->hasValidData( 'use_es_statistic' ) && $form->use_es_statistic == true ) {
        $data['use_es_statistic'] = 1;
    } else {
        $data['use_es_statistic'] = 0;
    }

    if ( $form->hasValidData( 'use_es_prev_chats' ) && $form->use_es_prev_chats == true ) {
        $data['use_es_prev_chats'] = 1;
    } else {
        $data['use_es_prev_chats'] = 0;
    }
            
    if ( $form->hasValidData( 'last_index_msg_id' )) {
        $data['last_index_msg_id'] = $form->last_index_msg_id ;
    } else {
        $data['last_index_msg_id'] = 0;
    }

    if ( $form->hasValidData( 'use_es_prev_chats_id' )) {
        $data['use_es_prev_chats_id'] = $form->use_es_prev_chats_id ;
    } else {
        $data['use_es_prev_chats_id'] = 0;
    }

    if ( $form->hasValidData( 'indexType' )) {
        $data['index_type'] = $form->indexType ;
    } else {
        $data['index_type'] = 'static';
    }
     
    $esOptions->explain = '';
    $esOptions->type = 0;
    $esOptions->hidden = 1;
    $esOptions->identifier = 'elasticsearch_options';
    $esOptions->value = serialize($data);
    $esOptions->saveThis();
    
    $tpl->set('updated','done');
}

$tpl->set('es_options',$data);

$Result['content'] = $tpl->fetch();

$Result['path'] = array(
    array(
        'url' => erLhcoreClassDesign::baseurl('elasticsearch/index'),
        'title' => erTranslationClassLhTranslation::getInstance()->getTranslation('lhelasticsearch/module', 'Elastic Search')
    ),
    array(
        'title' => erTranslationClassLhTranslation::getInstance()->getTranslation('lhelasticsearch/module', 'Options')
    )
);

?>