<?php

$tpl = erLhcoreClassTemplate::getInstance('elasticsearch/interactions.tpl.php');

$filterParams = erLhcoreClassSearchHandler::getParams(array(
    'customfilterfile' => 'extension/elasticsearch/classes/filter/interactions_list.php',
    'format_filter' => true,
    'use_override' => true,
    'uparams' => $Params['user_parameters_unordered']
));

$filterParams['is_search'] = false;

$tpl->set('input', $filterParams['input_form']);

$sparams = array(
    'body' => array()
);

$dateFilter = array(
    'gte' => (time() - (3 * 31 * 24 * 3600)),
    'lte' => (time() + (31 * 24 * 3600))
);

$minimumMatch = 0;

if (trim($filterParams['input_form']->attr) != '') {
    if ($filterParams['input_form']->attr == 'email'){
        $booldConditions['bool']['should'][]['term']['email'] = $filterParams['input_form']->val;
        $booldConditions['bool']['should'][]['term']['from_address'] = $filterParams['input_form']->val;
        $sparams['body']['query']['bool']['should'][] = $booldConditions;
        $minimumMatch = 1;
        $sparams['body']['query']['bool']['minimum_should_match'] = 1;
    } elseif ($filterParams['input_form']->attr == 'phone') {
        $sparams['body']['query']['bool']['must'][]['term']['phone'] = $filterParams['input_form']->val;
    }
}

if (trim($filterParams['input_form']->keyword) != '') {

    $exactMatch = $filterParams['input_form']->exact_match == 1 ? 'match_phrase' : 'match';
    $booldConditions = [];

    $paramQuery = [
        'query' => $filterParams['input_form']->keyword
    ];

    if ($filterParams['input_form']->fuzzy == 1 && $filterParams['input_form']->exact_match != 1) {
        $paramQuery['fuzziness'] = 'AUTO';
        $paramQuery['prefix_length'] = max((mb_strlen($filterParams['input_form']->keyword) - (is_numeric($filterParams['input_form']->fuzzy_prefix) ? $filterParams['input_form']->fuzzy_prefix : 1)),0);
    }

    if (empty($filterParams['input_form']->search_in) || in_array(1,$filterParams['input_form']->search_in)) {
        $booldConditions['bool']['should'][][$exactMatch]['msg_visitor'] = $paramQuery;
        $booldConditions['bool']['should'][][$exactMatch]['msg_operator'] = $paramQuery;
        $booldConditions['bool']['should'][][$exactMatch]['msg_system'] = $paramQuery;
        $booldConditions['bool']['should'][][$exactMatch]['subject'] = $paramQuery;
        $booldConditions['bool']['should'][][$exactMatch]['alt_body'] = $paramQuery;
    } else {
        if (in_array(2,$filterParams['input_form']->search_in)) {
            $booldConditions['bool']['should'][][$exactMatch]['msg_visitor'] = $paramQuery;
        }

        if (in_array(3,$filterParams['input_form']->search_in)) {
            $booldConditions['bool']['should'][][$exactMatch]['msg_operator'] = $paramQuery;
        }

        if (in_array(4,$filterParams['input_form']->search_in)) {
            $booldConditions['bool']['should'][][$exactMatch]['msg_system'] = $paramQuery;
        }

        if (in_array(5,$filterParams['input_form']->search_in)) {
            $booldConditions['bool']['should'][][$exactMatch]['subject'] = $paramQuery;
        }

        if (in_array(6,$filterParams['input_form']->search_in)) {
            $booldConditions['bool']['should'][][$exactMatch]['alt_body'] = $paramQuery;
        }
    }

    $sparams['body']['query']['bool']['should'][] = $booldConditions;

    $minimumMatch = $minimumMatch + 1;
    $sparams['body']['query']['bool']['minimum_should_match'] = $minimumMatch; // Minimum one condition should be matched

    $sparams['body']['highlight']['order'] = 'score';
    $sparams['body']['highlight']['fragment_size'] = 40;
    $sparams['body']['highlight']['number_of_fragments'] = 1;
    $sparams['body']['highlight']['fields']['msg_operator'] = new stdClass();
    $sparams['body']['highlight']['fields']['msg_visitor'] = new stdClass();
    $sparams['body']['highlight']['fields']['msg_system'] = new stdClass();
    $sparams['body']['highlight']['fields']['subject'] = new stdClass();
    $sparams['body']['highlight']['fields']['alt_body'] = new stdClass();
}

if (isset($filterParams['filter']['filtergte']['time'])) {
    $sparams['body']['query']['bool']['must'][]['range']['time']['gte'] = $filterParams['filter']['filtergte']['time'] * 1000;
    $dateFilter['gte'] = $filterParams['filter']['filtergte']['time'];
}

if (isset($filterParams['filter']['filterlte']['time'])) {
    $sparams['body']['query']['bool']['must'][]['range']['time']['lte'] = $filterParams['filter']['filterlte']['time'] * 1000;
    $dateFilter['lte'] = $filterParams['filter']['filterlte']['time'];
}

if ($filterParams['input_form']->sort_chat == 'asc') {
    $sort = array('time' => array('order' => 'asc'));
} elseif ($filterParams['input_form']->sort_chat == 'relevance') {
    $sort = array('_score' => array('order' => 'desc'));
} else {
    $sort = array('time' => array('order' => 'desc'));
}

$append = erLhcoreClassSearchHandler::getURLAppendFromInput($filterParams['input_form']);

$elasticSearchHandler = erLhcoreClassElasticClient::getHandler();

$indexSearch = erLhcoreClassElasticSearchStatistic::getIndexByFilter([
    'filtergte' => ['time' => $dateFilter['gte'] ],
    'filterlte' => ['time' => $dateFilter['lte'] ]
], erLhcoreClassModelESChat::$elasticType);

$indexSearch .=',' . erLhcoreClassElasticSearchStatistic::getIndexByFilter([
        'filtergte' => ['time' => $dateFilter['gte'] ],
        'filterlte' => ['time' => $dateFilter['lte'] ]
    ], erLhcoreClassModelESMail::$elasticType);

\erLhcoreClassChatEventDispatcher::getInstance()->dispatch('elasticsearch.interactions_index', array(
    'index_search' => & $indexSearch,
    'date_filter' => $dateFilter,
));

$sparamsCount = $sparams;
$sparamsCount['index'] = $indexSearch;
$sparamsCount['ignore_unavailable'] = true;

if (isset($sparamsCount['body']['highlight'])) {
    unset($sparamsCount['body']['highlight']);
}

$total = $elasticSearchHandler->count($sparamsCount)['count'];

$tpl->set('total_literal',$total);

$pages = new lhPaginator();
$pages->serverURL = erLhcoreClassDesign::baseurl('elasticsearch/interactions') . $append;
$pages->items_total = $total > 9000 ? 9000 : $total;
$pages->setItemsPerPage(15);
$pages->paginate();

if ($pages->items_total > 0) {

    $sparams['size'] = $pages->items_per_page;
    $sparams['from'] = $pages->low;
    $sparams['index'] = $indexSearch;
    $sparams['ignore_unavailable'] = true;
    $sparams['body']['sort'] = $sort;

    $response = $elasticSearchHandler->search($sparams);

    $chats = [];

    if (isset($response['hits']['hits']) && !empty($response['hits']['hits'])) {
        foreach ($response['hits']['hits'] as $doc) {
            if (strpos($doc['_index'],'lh_mail') !== false) {
                $className = 'erLhcoreClassModelESMail';
            } else {
                $className = 'erLhcoreClassModelESChat';
            }

            \erLhcoreClassChatEventDispatcher::getInstance()->dispatch('elasticsearch.interactions_class', array(
                'class_name' => & $className,
                'index' => $doc['_index'],
            ));

            $obj = new $className();
            $obj->setState($doc['_source']);
            $obj->id = $doc['_id'];

            $metaData = array('score' => $doc['_score'], 'index' => $doc['_index']);
            if (isset($doc['highlight'])) {
                $metaData['highlight'] = $doc['highlight'];
            }

            $obj->meta_data = $metaData;
            $chats[$obj->id] = $obj;
        }
    }

    $tpl->set('items', $chats);
}

$tpl->set('pages', $pages);


$tpl->set('Result',['path' => array(
    array(
        'url' => erLhcoreClassDesign::baseurl('elasticsearch/index'),
        'title' => erTranslationClassLhTranslation::getInstance()->getTranslation('lhelasticsearch/module', 'Elastic Search')
    ),
    array(
        'url' => erLhcoreClassDesign::baseurl('elasticsearch/interactions'),
        'title' => erTranslationClassLhTranslation::getInstance()->getTranslation('lhelasticsearch/list', 'Interactions list')
    )
)]);

$Result['body_class'] = 'h-100 dashboard-height';
$Result['content'] = $tpl->fetch();