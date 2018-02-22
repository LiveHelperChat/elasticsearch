<?php

$tpl = erLhcoreClassTemplate::getInstance('elasticsearch/getmapping.tpl.php');

$tpl->set('stats',erLhcoreClassElasticClient::getHandler()->indices()->stats(array('index' => $Params['user_parameters']['indice'])));
$tpl->set('mapping',erLhcoreClassElasticClient::getHandler()->indices()->getMapping(array('index' => $Params['user_parameters']['indice'])));

$Result['content'] = $tpl->fetch();
$Result['path'] = array(
    array(
        'url' => erLhcoreClassDesign::baseurl('elasticsearch/index'),
        'title' => erTranslationClassLhTranslation::getInstance()->getTranslation('lhelasticsearch/module', 'Elastic Search')
    ),
    array(
        'url' => erLhcoreClassDesign::baseurl('elasticsearch/indices'),
        'title' => erTranslationClassLhTranslation::getInstance()->getTranslation('lhelasticsearch/module', 'Indices')
    ),
    array(
        'url' => erLhcoreClassDesign::baseurl('elasticsearch/list'),
        'title' => erTranslationClassLhTranslation::getInstance()->getTranslation('lhelasticsearch/list', 'Chat list')
    )
);

?>