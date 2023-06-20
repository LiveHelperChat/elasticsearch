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
        'use_php_resque' => new ezcInputFormDefinitionElement(
            ezcInputFormDefinitionElement::OPTIONAL, 'boolean'
        ),
        'last_index_msg_id' => new ezcInputFormDefinitionElement(
            ezcInputFormDefinitionElement::OPTIONAL, 'int'
        ),
        'last_index_part_id' => new ezcInputFormDefinitionElement(
            ezcInputFormDefinitionElement::OPTIONAL, 'int'
        ),
        'use_es_prev_chats_id' => new ezcInputFormDefinitionElement(
            ezcInputFormDefinitionElement::OPTIONAL, 'boolean'
        ),
        'disable_es' => new ezcInputFormDefinitionElement(
            ezcInputFormDefinitionElement::OPTIONAL, 'boolean'
        ),
        'disable_es_mail' => new ezcInputFormDefinitionElement(
            ezcInputFormDefinitionElement::OPTIONAL, 'boolean'
        ),
        'auto_enable' => new ezcInputFormDefinitionElement(
            ezcInputFormDefinitionElement::OPTIONAL, 'boolean'
        ),
        'check_if_exists' => new ezcInputFormDefinitionElement(
            ezcInputFormDefinitionElement::OPTIONAL, 'boolean'
        ),
        'indexType' => new ezcInputFormDefinitionElement(
            ezcInputFormDefinitionElement::OPTIONAL, 'string'
        ),
        'report_email_es' => new ezcInputFormDefinitionElement(
            ezcInputFormDefinitionElement::OPTIONAL, 'unsafe_raw'
        ),
        'star_month_index' => new ezcInputFormDefinitionElement(
            ezcInputFormDefinitionElement::OPTIONAL, 'boolean'
        )
    );

    $form = new ezcInputForm( INPUT_POST, $definition );
    $Errors = array();
            
    if ( $form->hasValidData( 'use_es_statistic' ) && $form->use_es_statistic == true ) {
        $data['use_es_statistic'] = 1;
    } else {
        $data['use_es_statistic'] = 0;
    }

    if ( $form->hasValidData( 'disable_es' ) && $form->disable_es == true ) {
        $data['disable_es'] = 1;
    } else {
        $data['disable_es'] = 0;
    }

    if ( $form->hasValidData( 'disable_es_mail' ) && $form->disable_es_mail == true ) {
        $data['disable_es_mail'] = 1;
    } else {
        $data['disable_es_mail'] = 0;
    }

    if ( $form->hasValidData( 'check_if_exists' ) && $form->check_if_exists == true ) {
        $data['check_if_exists'] = 1;
    } else {
        $data['check_if_exists'] = 0;
    }

    if ( $form->hasValidData( 'star_month_index' ) && $form->star_month_index == true ) {
        $data['star_month_index'] = 1;
    } else {
        $data['star_month_index'] = 0;
    }

    if ( $form->hasValidData( 'auto_enable' ) && $form->auto_enable == true ) {
        $data['auto_enable'] = 1;
    } else {
        $data['auto_enable'] = 0;
    }

    if ( $form->hasValidData( 'use_es_prev_chats' ) && $form->use_es_prev_chats == true ) {
        $data['use_es_prev_chats'] = 1;
    } else {
        $data['use_es_prev_chats'] = 0;
    }

    if ( $form->hasValidData( 'use_php_resque' ) && $form->use_php_resque == true ) {
        $data['use_php_resque'] = 1;
    } else {
        $data['use_php_resque'] = 0;
    }
            
    if ( $form->hasValidData( 'last_index_msg_id' )) {
        $data['last_index_msg_id'] = $form->last_index_msg_id ;
    } else {
        $data['last_index_msg_id'] = 0;
    }
    
    if ( $form->hasValidData( 'last_index_part_id' )) {
        $data['last_index_part_id'] = $form->last_index_part_id ;
    } else {
        $data['last_index_part_id'] = 0;
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

    if ( $form->hasValidData( 'report_email_es' )) {
        $data['report_email_es'] = $form->report_email_es ;
    } else {
        $data['report_email_es'] = '';
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