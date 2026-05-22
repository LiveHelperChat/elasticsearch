<?php
$tpl = erLhcoreClassTemplate::getInstance('elasticsearch/listlog.tpl.php');

if (isset($_GET['doSearch'])) {
    $filterParams = erLhcoreClassSearchHandler::getParams(array(
        'customfilterfile' => 'extension/elasticsearch/classes/filter/log_list.php',
        'format_filter' => true,
        'use_override' => true,
        'uparams' => $Params['user_parameters_unordered']
    ));
    $filterParams['is_search'] = true;
} else {
    $filterParams = erLhcoreClassSearchHandler::getParams(array(
        'customfilterfile' => 'extension/elasticsearch/classes/filter/log_list.php',
        'format_filter' => true,
        'uparams' => $Params['user_parameters_unordered']
    ));
    $filterParams['is_search'] = false;
}

$sparams = array(
    'body' => array()
);

if (trim((string)$filterParams['input_form']->chat_id) != '') {
    $sparams['body']['query']['bool']['must'][]['term']['chat_id'] = (int)trim($filterParams['input_form']->chat_id);
}

$append = erLhcoreClassSearchHandler::getURLAppendFromInput($filterParams['input_form']);

$pages = new lhPaginator();
$pages->serverURL = erLhcoreClassDesign::baseurl('elasticsearch/listlog') . $append;
$pages->items_total = \LiveHelperChatExtension\elasticsearch\providers\Index\RestLog::getCount($sparams);
$pages->setItemsPerPage(30);
$pages->paginate();
$tpl->set('items',[]);

if ($pages->items_total > 0) {
    $tpl->set('items', \LiveHelperChatExtension\elasticsearch\providers\Index\RestLog::getList(array(
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
$tpl->set('input', $filterParams['input_form']);
$tpl->set('filterParams', $filterParams);

$Result['content'] = $tpl->fetch();
$Result['path'] = array(
    array(
        'url' => erLhcoreClassDesign::baseurl('elasticsearch/index'),
        'title' => erTranslationClassLhTranslation::getInstance()->getTranslation('lhelasticsearch/module', 'Elastic Search')
    ),
    array(
        'url' => erLhcoreClassDesign::baseurl('elasticsearch/listlog'),
        'title' => erTranslationClassLhTranslation::getInstance()->getTranslation('lhelasticsearch/list', 'REST API Log')
    )
);
