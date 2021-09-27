<?php
$tpl = erLhcoreClassTemplate::getInstance('elasticsearch/indices.tpl.php');


if (ezcInputForm::hasPostData()) {

    if (!isset($_POST['csfr_token']) || !$currentUser->validateCSFRToken($_POST['csfr_token'])) {
        erLhcoreClassModule::redirect('elasticsearch/indices');
        exit;
    }

    foreach ($_POST['indices'] as $indice) {
        erLhcoreClassElasticClient::getHandler()->indices()->delete(array(
            'index' => $indice
        ));
    }
}


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
