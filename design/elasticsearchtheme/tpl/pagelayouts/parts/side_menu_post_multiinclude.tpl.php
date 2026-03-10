<?php
if (erLhcoreClassUser::instance()->hasAccessTo('lhelasticsearch', 'use')) {
    $menuItems[] = array('identifier' => 'elastic_chats', 'href' => erLhcoreClassDesign::baseurl('elasticsearch/list'), 'iclass' => 'search', 'text' => erTranslationClassLhTranslation::getInstance()->getTranslation('pagelayout/pagelayout','Search'));
}
?>