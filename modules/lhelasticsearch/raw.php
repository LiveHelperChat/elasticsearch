<?php

$tpl = erLhcoreClassTemplate::getInstance('elasticsearch/raw.tpl.php');

if (strpos($Params['user_parameters']['index'],'lh_mail') !== false) {
    $className = 'erLhcoreClassModelESMail';
} else {
    $className = 'erLhcoreClassModelESChat';
}

\erLhcoreClassChatEventDispatcher::getInstance()->dispatch('elasticsearch.interactions_class', array(
    'class_name' => & $className,
    'index' => $Params['user_parameters']['index'],
));

$tpl->set('item', call_user_func_array($className.'::fetch',[
    $Params['user_parameters']['id'],
    $Params['user_parameters']['index']
]));

$Result['content'] = $tpl->fetch();
$Result['path'] = array(
    array(
        'url' => erLhcoreClassDesign::baseurl('elasticsearch/index'),
        'title' => erTranslationClassLhTranslation::getInstance()->getTranslation('lhelasticsearch/module', 'Elastic Search')
    ),
    array(
        'url' => erLhcoreClassDesign::baseurl('elasticsearch/list'),
        'title' => erTranslationClassLhTranslation::getInstance()->getTranslation('lhelasticsearch/list', 'Chat list')
    ),
    array(
        'title' => erTranslationClassLhTranslation::getInstance()->getTranslation('lhelasticsearch/list', 'Raw information')
    )
);
