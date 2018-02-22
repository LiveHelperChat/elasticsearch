<?php
$tpl = erLhcoreClassTemplate::getInstance('elasticsearch/indices.tpl.php');

$tpl->set('indices',erLhcoreClassElasticClient::getHandler()->indices()->getAliases(array('index' => 'chat*')));

$Result['content'] = $tpl->fetch();
$Result['path'] = array(
    array(
        'url' => erLhcoreClassDesign::baseurl('elasticsearch/index'),
        'title' => erTranslationClassLhTranslation::getInstance()->getTranslation('lhelasticsearch/module', 'Elastic Search')
    ),
    array(
        'url' => erLhcoreClassDesign::baseurl('elasticsearch/list'),
        'title' => erTranslationClassLhTranslation::getInstance()->getTranslation('lhelasticsearch/list', 'Indices')
    )
);
