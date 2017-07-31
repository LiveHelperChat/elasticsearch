<?php
$tpl = erLhcoreClassTemplate::getInstance('elasticsearch/list.tpl.php');

$validTabs = array('chats','messages');

$tab = (isset($Params['user_parameters_unordered']['tab']) && in_array($Params['user_parameters_unordered']['tab'], $validTabs)) ? $Params['user_parameters_unordered']['tab'] : "chats";
$tpl->set('tab', $tab);

// Chats filter
if (isset($_GET['doSearch'])) {
    $filterParams = erLhcoreClassSearchHandler::getParams(array(
        'customfilterfile' => 'extension/elasticsearch/classes/filter/chat_list.php',
        'format_filter' => true,
        'use_override' => true,
        'uparams' => $Params['user_parameters_unordered']
    ));
    $filterParams['is_search'] = true;
} else {
    $filterParams = erLhcoreClassSearchHandler::getParams(array(
        'customfilterfile' => 'extension/elasticsearch/classes/filter/chat_list.php',
        'format_filter' => true,
        'uparams' => $Params['user_parameters_unordered']
    ));
    $filterParams['is_search'] = false;
}

$tpl->set('input', $filterParams['input_form']);

// Messages filter
if (isset($_GET['doSearch'])) {
    $filterParamsMsg = erLhcoreClassSearchHandler::getParams(array(
        'customfilterfile' => 'extension/elasticsearch/classes/filter/chat_msg.php',
        'format_filter' => true,
        'use_override' => true,
        'uparams' => $Params['user_parameters_unordered']
    ));
    $filterParamsMsg['is_search'] = true;
} else {
    $filterParamsMsg = erLhcoreClassSearchHandler::getParams(array(
        'customfilterfile' => 'extension/elasticsearch/classes/filter/chat_msg.php',
        'format_filter' => true,
        'uparams' => $Params['user_parameters_unordered']
    ));
    $filterParamsMsg['is_search'] = false;
}

$tpl->set('input_msg', $filterParamsMsg['input_form']);

if ($tab == 'chats') {    
            
    $sparams = array(
        'body' => array()
    );
    
    if ($filterParams['input_form']->chat_id != '') {
        $sparams['body']['query']['bool']['must'][]['term']['chat_id'] = $filterParams['input_form']->chat_id;
    }
    
    if ($filterParams['input_form']->nick != '') {
        $sparams['body']['query']['bool']['must'][]['match']['nick'] = $filterParams['input_form']->nick;
    }
    
    $append = erLhcoreClassSearchHandler::getURLAppendFromInput($filterParams['input_form']);
    
    $pages = new lhPaginator();
    $pages->serverURL = erLhcoreClassDesign::baseurl('elasticsearch/list') . $append;
    $pages->items_total = erLhcoreClassModelESChat::getCount($sparams);
    $pages->setItemsPerPage(30);
    $pages->paginate();
    
    if ($pages->items_total > 0) {
        $tpl->set('items', erLhcoreClassModelESChat::getList(array(
            'offset' => $pages->low,
            'limit' => $pages->items_per_page,
            'body' => array_merge(array(
                'sort' => array(
                    'time' => array(
                        'order' => 'desc'
                    )
                )
            ), $sparams['body'])
        )));
    }
    
    $tpl->set('pages', $pages);
        
} else {
    
    $sparams = array(
        'body' => array()
    );
    
    if ($filterParamsMsg['input_form']->message_text != '') {
        $sparams['body']['query']['bool']['must'][]['match']['msg'] = $filterParamsMsg['input_form']->message_text;
    }
    
    $append = erLhcoreClassSearchHandler::getURLAppendFromInput($filterParamsMsg['input_form']);
    
    $pages = new lhPaginator();
    $pages->serverURL = erLhcoreClassDesign::baseurl('elasticsearch/list') .'/(tab)/messages' . $append;
    $pages->items_total = erLhcoreClassModelESMsg::getCount($sparams);
    $pages->setItemsPerPage(30);
    $pages->paginate();
    
    if ($pages->items_total > 0) {
        $tpl->set('items', erLhcoreClassModelESMsg::getList(array(
            'offset' => $pages->low,
            'limit' => $pages->items_per_page,
            'body' => array_merge(array(
                'sort' => array(
                    '_score' => array(
                        'order' => 'desc'
                    )
                )
            ), $sparams['body'])
        )));
    }
    
    $tpl->set('pages', $pages);        
}



$Result['content'] = $tpl->fetch();
$Result['path'] = array(
    array(
        'url' => erLhcoreClassDesign::baseurl('elasticsearch/index'),
        'title' => erTranslationClassLhTranslation::getInstance()->getTranslation('lhelasticsearch/module', 'Elastic Search')
    ),
    array(
        'url' => erLhcoreClassDesign::baseurl('elasticsearch/list'),
        'title' => erTranslationClassLhTranslation::getInstance()->getTranslation('lhelasticsearch/list', 'Chat list')
    )
);
