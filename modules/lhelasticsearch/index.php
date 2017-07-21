<?php
$tpl = erLhcoreClassTemplate::getInstance('elasticsearch/index.tpl.php');
$Result['content'] = $tpl->fetch();
$Result['path'] = array(
    array(
        'url' => erLhcoreClassDesign::baseurl('elasticsearch/index'),
        'title' => erTranslationClassLhTranslation::getInstance()->getTranslation('lhelasticsearch/module', 'Elastic Search')
    )
);

?>