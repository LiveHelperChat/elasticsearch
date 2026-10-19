<?php
$tpl = erLhcoreClassTemplate::getInstance('elasticsearch/listov.tpl.php');

if (isset($_GET['doSearch'])) {
    $filterParams = erLhcoreClassSearchHandler::getParams(array(
        'customfilterfile' => 'extension/elasticsearch/classes/filter/ov_list.php',
        'format_filter' => true,
        'use_override' => true,
        'uparams' => $Params['user_parameters_unordered']
    ));
    $filterParams['is_search'] = true;
} else {
    $filterParams = erLhcoreClassSearchHandler::getParams(array(
        'customfilterfile' => 'extension/elasticsearch/classes/filter/ov_list.php',
        'format_filter' => true,
        'use_override' => true,
        'uparams' => $Params['user_parameters_unordered']
    ));
    $filterParams['is_search'] = false;
}

$sparams = array(
    'body' => array()
);

// Keyword search in current page, page title and referrer
if (trim((string)$filterParams['input_form']->keyword) != '') {

    $exactMatch = $filterParams['input_form']->exact_match == 1 ? 'match_phrase' : 'match';

    $paramQuery = array(
        'query' => $filterParams['input_form']->keyword
    );

    if ($filterParams['input_form']->fuzzy == 1 && $filterParams['input_form']->exact_match != 1 && $filterParams['input_form']->expression != 1) {
        $paramQuery['fuzziness'] = 'AUTO';
        $paramQuery['prefix_length'] = max((mb_strlen($filterParams['input_form']->keyword) - (is_numeric($filterParams['input_form']->fuzzy_prefix) ? (int)$filterParams['input_form']->fuzzy_prefix : 1)), 0);
    }

    $searchIn = is_array($filterParams['input_form']->search_in) ? $filterParams['input_form']->search_in : array();

    $searchFields = array();

    if (empty($searchIn) || in_array(1, $searchIn)) {
        $searchFields[] = 'current_page';
    }

    if (empty($searchIn) || in_array(2, $searchIn)) {
        $searchFields[] = 'page_title';
    }

    if (empty($searchIn) || in_array(3, $searchIn)) {
        $searchFields[] = 'referrer';
    }

    if ($filterParams['input_form']->expression == 1) {
        $queryStringParam = $paramQuery;
        $queryStringParam['fields'] = $searchFields;
        $sparams['body']['query']['bool']['must'][]['query_string'] = $queryStringParam;
    } else {
        foreach ($searchFields as $searchField) {
            $sparams['body']['query']['bool']['should'][][$exactMatch][$searchField] = $paramQuery;
        }
        $sparams['body']['query']['bool']['minimum_should_match'] = 1;
    }

    $sparams['body']['highlight']['order'] = 'score';
    $sparams['body']['highlight']['fragment_size'] = 40;
    $sparams['body']['highlight']['number_of_fragments'] = 3;
    $sparams['body']['highlight']['fields']['current_page'] = new stdClass();
    $sparams['body']['highlight']['fields']['page_title'] = new stdClass();
    $sparams['body']['highlight']['fields']['referrer'] = new stdClass();
}

// IP filter
if (trim((string)$filterParams['input_form']->ip) != '') {
    $ipFilter = trim($filterParams['input_form']->ip);

    if (filter_var($ipFilter, FILTER_VALIDATE_IP) !== false) {
        $sparams['body']['query']['bool']['must'][]['term']['ip'] = $ipFilter;
    } else {
        // Invalid IP, force no results instead of sending a malformed query to Elasticsearch
        $sparams['body']['query']['bool']['must'][]['match_none'] = new stdClass();
    }
}

// Department filter
if (isset($filterParams['input']->department_ids) && is_array($filterParams['input']->department_ids) && !empty($filterParams['input']->department_ids)) {
    erLhcoreClassChat::validateFilterIn($filterParams['input']->department_ids);
    $sparams['body']['query']['bool']['must'][]['terms']['dep_id'] = array_map('intval', $filterParams['input']->department_ids);
}

// Date range filter, dates are stored in milliseconds
if (isset($filterParams['filter']['filtergte']['last_visit'])) {
    $sparams['body']['query']['bool']['must'][]['range']['last_visit']['gte'] = (int)$filterParams['filter']['filtergte']['last_visit'] * 1000;
}

if (isset($filterParams['filter']['filterlte']['last_visit'])) {
    $sparams['body']['query']['bool']['must'][]['range']['last_visit']['lte'] = (int)$filterParams['filter']['filterlte']['last_visit'] * 1000;
}

$append = erLhcoreClassSearchHandler::getURLAppendFromInput($filterParams['input_form']);

if ($filterParams['input_form']->sort_ov == 'last_visit_asc') {
    $sort = array('last_visit' => array('order' => 'asc'));
} elseif ($filterParams['input_form']->sort_ov == 'first_visit_desc') {
    $sort = array('first_visit' => array('order' => 'desc'));
} elseif ($filterParams['input_form']->sort_ov == 'first_visit_asc') {
    $sort = array('first_visit' => array('order' => 'asc'));
} elseif ($filterParams['input_form']->sort_ov == 'time_on_site_desc') {
    $sort = array('time_on_site' => array('order' => 'desc'));
} elseif ($filterParams['input_form']->sort_ov == 'pages_count_desc') {
    $sort = array('pages_count' => array('order' => 'desc'));
} elseif ($filterParams['input_form']->sort_ov == 'total_visits_desc') {
    $sort = array('total_visits' => array('order' => 'desc'));
} elseif ($filterParams['input_form']->sort_ov == 'relevance') {
    $sort = array('_score' => array('order' => 'desc'));
} else {
    $sort = array('last_visit' => array('order' => 'desc'));
}

$pages = new lhPaginator();
$pages->serverURL = erLhcoreClassDesign::baseurl('elasticsearch/listov') . $append;
$pages->items_total = \LiveHelperChatExtension\elasticsearch\providers\Index\OnlineVisitor::getCount($sparams);
$pages->setItemsPerPage(30);
$pages->paginate();

if ($pages->items_total > 0) {
    $tpl->set('items', \LiveHelperChatExtension\elasticsearch\providers\Index\OnlineVisitor::getList(array(
        'offset' => $pages->low,
        'limit' => $pages->items_per_page,
        'body' => array_merge(array(
            'sort' => $sort
        ), $sparams['body'])
    )));
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
        'url' => erLhcoreClassDesign::baseurl('elasticsearch/listov'),
        'title' => erTranslationClassLhTranslation::getInstance()->getTranslation('lhelasticsearch/list', 'Online Visitors')
    )
);
