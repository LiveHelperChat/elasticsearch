<?php

$tpl = erLhcoreClassTemplate::getInstance( 'elasticsearch/statisticpending.tpl.php');

$data = $_GET['date'];

$filterParams = erLhcoreClassSearchHandler::getParams(array('customfilterfile' => 'extension/elasticsearch/classes/searchattr/pendingvsonlineop.php', 'format_filter' => true, 'uparams' => $Params['user_parameters_unordered']));

$sparams = array();
$sparams['index'] = erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionElasticsearch')->settings['index_search'] . '-' . erLhcoreClassModelESPendingChat::$elasticType;
$sparams['ignore_unavailable'] = true;

$filterParamsIndex = array();

if (! isset($filterParams['filter']['filtergte']['itime']) && ! isset($filterParams['filter']['filterlte']['itime'])) {
    $dateFilter['gte'] = time()-(24*3600);
}

if (isset($filterParams['filter']['filtergte']['itime'])) {
    $dateFilter['gte'] = $filterParams['filter']['filtergte']['itime'];
}

if (isset($filterParams['filter']['filterlte']['itime'])) {
    $dateFilter['lte'] = $filterParams['filter']['filterlte']['itime'];
}

erLhcoreClassChatStatistic::formatUserFilter($filterParams);

$filterChats = $filterParams;

if (isset($filterChats['filter']['filterin']['lh_chat.user_id'])) {
    unset($filterChats['filter']['filterin']['lh_chat.user_id']);
}

if (isset($filterChats['filter']['filter']['user_id'])) {
    unset($filterChats['filter']['filter']['user_id']);
}

if (isset($filterChats['filter']['filterin']['user_id'])) {
    unset($filterChats['filter']['filterin']['user_id']);
}

$groupOptions = erLhcoreClassElasticSearchStatistic::getGroupBy();
if (!empty($filterParams['input_form']->group_by) && isset($groupOptions[$filterParams['input_form']->group_by])) {
    $groupByData = array(
        'interval' => (int)$filterParams['input_form']->group_by,
        'divide' => round($filterParams['input_form']->group_by/(60000))
    );
}

if (isset($groupByData['interval'])) {
    $filterChats['filter']['filtergte']['itime'] = strtotime($data);
    $filterChats['filter']['filterlt']['itime'] = strtotime($data) + ($groupByData['interval'] / 1000);
}

erLhcoreClassElasticSearchStatistic::formatFilter($filterChats['filter'], $sparams);

$sort = array('itime' => array('order' => 'asc'));

$chats = erLhcoreClassModelESPendingChat::getList(array(
    'offset' => 0,
    'limit' => 1000,
    'body' => array_merge(array(
        'sort' => $sort
    ), $sparams['body'])
),
array('date_index' => $dateFilter));

$tpl->set('chats', $chats);

/****************************/

$sparams = array();
$sparams['index'] = erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionElasticsearch')->settings['index_search'] . '-' . erLhcoreClassModelESOnlineOperator::$elasticType;
$sparams['ignore_unavailable'] = true;

if (isset($filterParams['filter']['filterin']['lh_chat.user_id']) && isset($filterParams['filter']['filterin']['user_id'])) {
    $mergedIds = array_unique(array_intersect($filterParams['filter']['filterin']['lh_chat.user_id'], $filterParams['filter']['filterin']['user_id']));
    if (!empty($mergedIds)){
        $filterParams['filter']['filterin']['user_id'] = $mergedIds;
    } else {
        $filterParams['filter']['filterin']['user_id'] = array(-1);
    }
    unset($filterParams['filter']['filterin']['lh_chat.user_id']);
}

if (isset($filterParams['filter']['filterin']['lh_chat.dep_id']) && isset($filterParams['filter']['filterin']['dep_id'])) {

    $mergedIds = array_unique(array_intersect($filterParams['filter']['filterin']['lh_chat.dep_id'], $filterParams['filter']['filterin']['dep_id']));

    if (!empty($mergedIds)){
        $filterParams['filter']['filterinm']['dep_ids'] = $mergedIds;
    } else {
        $filterParams['filter']['filterinm']['dep_ids'] = array(-1);
    }

    unset($filterParams['filter']['filterin']['lh_chat.dep_id']);
    unset($filterParams['filter']['filterin']['dep_id']);

} else if (isset($filterParams['filter']['filterin']['lh_chat.dep_id'])) {
    $filterParams['filter']['filterinm']['dep_ids'] = $filterParams['filter']['filterin']['lh_chat.dep_id'];
    unset($filterParams['filter']['filterin']['lh_chat.dep_id']);
} else if (isset($filterParams['filter']['filterin']['dep_id'])) {
    $filterParams['filter']['filterinm']['dep_ids'] = $filterParams['filter']['filterin']['dep_id'];
    unset($filterParams['filter']['filterin']['dep_id']);
} elseif (isset($filterParams['filter']['filter']['dep_id'])) {
    $filterParams['filter']['filterm']['dep_ids'] = $filterParams['filter']['filter']['dep_id'];
    unset($filterParams['filter']['filter']['dep_id']);
};

if (isset($groupByData['interval'])) {
    $filterParams['filter']['filtergte']['itime'] = strtotime($data);
    $filterParams['filter']['filterlt']['itime'] = strtotime($data) + ($groupByData['interval'] / 1000);
}

erLhcoreClassElasticSearchStatistic::formatFilter($filterParams['filter'], $sparams);

$sort = array('itime' => array('order' => 'asc'));

$operators = erLhcoreClassModelESOnlineOperator::getList(array(
    'offset' => 0,
    'limit' => 1000,
    'body' => array_merge(array(
        'sort' => $sort
    ), $sparams['body'])
),
    array('date_index' => $dateFilter));

$tpl->set('operators', $operators);
$tpl->set('divide', isset($groupByData['divide']) ? $groupByData['divide'] : 1);

$Result['content'] = $tpl->fetch();
$Result['pagelayout'] = 'popup';
