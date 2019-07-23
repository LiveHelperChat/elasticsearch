<?php
$tpl = erLhcoreClassTemplate::getInstance('elasticsearch/listmsg.tpl.php');

if (isset($_GET['doSearch'])) {
    $filterParams = erLhcoreClassSearchHandler::getParams(array(
        'customfilterfile' => 'extension/elasticsearch/classes/filter/chat_msg.php',
        'format_filter' => true,
        'use_override' => true,
        'uparams' => $Params['user_parameters_unordered']
    ));
    $filterParams['is_search'] = true;
} else {
    $filterParams = erLhcoreClassSearchHandler::getParams(array(
        'customfilterfile' => 'extension/elasticsearch/classes/filter/chat_msg.php',
        'format_filter' => true,
        'uparams' => $Params['user_parameters_unordered']
    ));
    $filterParams['is_search'] = false;
}

$sparams = array();
$sparams['body']['query']['bool']['must'][]['term']['chat_id'] = $Params['user_parameters']['chat_id'];

$append = erLhcoreClassSearchHandler::getURLAppendFromInput($filterParams['input_form']);

// Set date range for archive
$itemsArchive[$Params['user_parameters']['chat_id']] = array();

erLhcoreClassChatArcive::setArchiveAttribute($itemsArchive);

if (isset($itemsArchive[$Params['user_parameters']['chat_id']]) && $itemsArchive[$Params['user_parameters']['chat_id']]['archive'] == true){
    $archive = erLhcoreClassModelChatArchiveRange::fetch($itemsArchive[$Params['user_parameters']['chat_id']]['archive_id']);
    $archive->setTables();
    $chat = erLhcoreClassModelChatArchive::fetch($Params['user_parameters']['chat_id']);
    $dateFilter['gte'] = $chat->time;
} else {
    $chat = erLhcoreClassModelChat::fetch($Params['user_parameters']['chat_id']);
    $dateFilter['gte'] = $chat->time;
}

$pages = new lhPaginator();
$pages->serverURL = erLhcoreClassDesign::baseurl('elasticsearch/listmsg') .'/' . $Params['user_parameters']['chat_id']  . $append;
$pages->items_total = erLhcoreClassModelESMsg::getCount($sparams, array('date_index' => $dateFilter));
$pages->setItemsPerPage(30);
$pages->paginate();

if ($pages->items_total > 0) {
    $tpl->set('items', erLhcoreClassModelESMsg::getList(array(
        'offset' => $pages->low,
        'limit' => $pages->items_per_page,
        'body' => array_merge(array(
            'sort' => array(
                'time' => array(
                    'order' => 'asc'
                )
            )
        ), $sparams['body'])
    ),array('date_index' => $dateFilter))
    );
}

$tpl->set('pages', $pages);
$tpl->set('input', $filterParams['input_form']);

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
        'title' => erTranslationClassLhTranslation::getInstance()->getTranslation('lhelasticsearch/list', 'Chat messages')
    )
);
