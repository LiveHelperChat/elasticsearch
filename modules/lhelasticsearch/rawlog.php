<?php

$tpl = erLhcoreClassTemplate::getInstance('elasticsearch/raw.tpl.php');

$tpl->set('item', \LiveHelperChatExtension\elasticsearch\providers\Index\RestLog::fetch($Params['user_parameters']['id'], $Params['user_parameters']['index']));

$Result['content'] = $tpl->fetch();
$Result['path'] = array(
    array(
        'url' => erLhcoreClassDesign::baseurl('elasticsearch/index'),
        'title' => erTranslationClassLhTranslation::getInstance()->getTranslation('lhelasticsearch/module', 'Elastic Search')
    ),
    array(
        'url' => erLhcoreClassDesign::baseurl('elasticsearch/listlog'),
        'title' => erTranslationClassLhTranslation::getInstance()->getTranslation('lhelasticsearch/list', 'REST API Log list')
    ),
    array(
        'title' => erTranslationClassLhTranslation::getInstance()->getTranslation('lhelasticsearch/list', 'Raw information')
    )
);
