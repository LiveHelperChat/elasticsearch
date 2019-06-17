<?php
$tpl = erLhcoreClassTemplate::getInstance('elasticsearch/list.tpl.php');

$validTabs = array('chats','messages');

$tab = (isset($Params['user_parameters_unordered']['tab']) && in_array($Params['user_parameters_unordered']['tab'], $validTabs)) ? $Params['user_parameters_unordered']['tab'] : "chats";
$tpl->set('tab', $tab);

// Chats filter
if (isset($_GET['ds'])) {
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
if (isset($_GET['ds'])) {
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

    $dateFilter = array();

    if (trim($filterParams['input_form']->chat_id) != '') {
        $sparams['body']['query']['bool']['must'][]['term']['chat_id'] = (int)trim($filterParams['input_form']->chat_id);
    }
    
    if ($filterParams['input_form']->nick != '') {
        $sparams['body']['query']['bool']['must'][]['match']['nick'] = $filterParams['input_form']->nick;
    }
    
    if ($filterParams['input_form']->email != '') {
        $sparams['body']['query']['bool']['must'][]['match']['email'] = $filterParams['input_form']->email;
    }
    
    if (trim($filterParams['input_form']->user_id) != '') {
        $sparams['body']['query']['bool']['must'][]['term']['user_id'] = (int)trim($filterParams['input_form']->user_id);
    }
    
    if (trim($filterParams['input_form']->department_id) != '') {
        $sparams['body']['query']['bool']['must'][]['term']['dep_id'] = (int)trim($filterParams['input_form']->department_id);
    }

    if (trim($filterParams['input_form']->invitation_id) != '') {
        $sparams['body']['query']['bool']['must'][]['term']['invitation_id'] = (int)trim($filterParams['input_form']->invitation_id);
    }

    if (trim($filterParams['input_form']->department_group_id) != '') {
        $db = ezcDbInstance::get();
        $stmt = $db->prepare('SELECT dep_id FROM lh_departament_group_member WHERE dep_group_id = :group_id');
        $stmt->bindValue( ':group_id', $filterParams['input']->department_group_id, PDO::PARAM_INT);
        $stmt->execute();
        $depIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($depIds)) {
            $sparams['body']['query']['bool']['must'][]['terms']['dep_id'] = $depIds;
        }
    }

    if (isset($filterParams['input']->group_id) && is_numeric($filterParams['input']->group_id) && $filterParams['input']->group_id > 0 ) {
        $db = ezcDbInstance::get();
        $stmt = $db->prepare('SELECT user_id FROM lh_groupuser WHERE group_id = :group_id');
        $stmt->bindValue( ':group_id', $filterParams['input']->group_id, PDO::PARAM_INT);
        $stmt->execute();
        $userIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($userIds)) {
            $sparams['body']['query']['bool']['must'][]['terms']['user_id'] = $userIds;
        }
    }

    if (isset($filterParams['input']->group_ids) && is_array($filterParams['input']->group_ids) && !empty($filterParams['input']->group_ids)) {

        erLhcoreClassChat::validateFilterIn($filterParams['input']->group_ids);

        $db = ezcDbInstance::get();
        $stmt = $db->prepare('SELECT user_id FROM lh_groupuser WHERE group_id IN (' . implode(',',$filterParams['input']->group_ids) .')');
        $stmt->execute();
        $userIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($userIds)) {
            $sparams['body']['query']['bool']['must'][]['terms']['user_id'] = $userIds;
        }
    }

    if (isset($filterParams['input']->department_group_ids) && is_array($filterParams['input']->department_group_ids) && !empty($filterParams['input']->department_group_ids)) {

        erLhcoreClassChat::validateFilterIn($filterParams['input']->department_group_ids);

        $db = ezcDbInstance::get();
        $stmt = $db->prepare('SELECT dep_id FROM lh_departament_group_member WHERE dep_group_id IN (' . implode(',',$filterParams['input']->department_group_ids) . ')');
        $stmt->execute();
        $depIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($depIds)) {
            $sparams['body']['query']['bool']['must'][]['terms']['dep_id'] = $depIds;
        }
    }

    if (isset($filterParams['input']->department_ids) && is_array($filterParams['input']->department_ids) && !empty($filterParams['input']->department_ids)) {
        erLhcoreClassChat::validateFilterIn($filterParams['input']->department_ids);
        $sparams['body']['query']['bool']['must'][]['terms']['dep_id'] = $filterParams['input']->department_ids;
    }

    if (isset($filterParams['input']->user_ids) && is_array($filterParams['input']->user_ids) && !empty($filterParams['input']->user_ids)) {
        erLhcoreClassChat::validateFilterIn($filterParams['input']->user_ids);
        $sparams['body']['query']['bool']['must'][]['terms']['user_id'] = $filterParams['input']->user_ids;
    }

    if ($filterParams['input_form']->no_user == 1) {
        $sparams['body']['query']['bool']['must'][]['term']['user_id'] = 0;
    }

    if (isset($filterParams['filter']['filtergte']['time'])) {
        $sparams['body']['query']['bool']['must'][]['range']['time']['gte'] = $filterParams['filter']['filtergte']['time'] * 1000;
        $dateFilter['gte'] = $filterParams['filter']['filtergte']['time'];
    }

    if (isset($filterParams['filter']['filterlte']['time'])) {
        $sparams['body']['query']['bool']['must'][]['range']['time']['lte'] = $filterParams['filter']['filterlte']['time'] * 1000;
        $dateFilter['lte'] = $filterParams['filter']['filterlte']['time'];
    }
    
    if (isset($filterParams['filter']['filtergt']['chat_duration'])) {
        $sparams['body']['query']['bool']['must'][]['range']['chat_duration']['gt'] = (int)$filterParams['filter']['filtergt']['chat_duration'];
    }
    
    if (isset($filterParams['filter']['filterlte']['chat_duration'])) {
        $sparams['body']['query']['bool']['must'][]['range']['chat_duration']['lte'] = (int)$filterParams['filter']['filterlte']['chat_duration'];
    }
    
    if (isset($filterParams['filter']['filtergt']['wait_time'])) {
        $sparams['body']['query']['bool']['must'][]['range']['wait_time']['gt'] = (int)$filterParams['filter']['filtergt']['wait_time'];
    }
    
    if (isset($filterParams['filter']['filterlte']['wait_time'])) {
        $sparams['body']['query']['bool']['must'][]['range']['wait_time']['lte'] = (int)$filterParams['filter']['filterlte']['wait_time'];
    }

    if (trim($filterParams['input_form']->uagent) != '') {
        $sparams['body']['query']['bool']['must'][]['match']['uagent'] = $filterParams['input_form']->uagent;
    }

    if (trim($filterParams['input_form']->keyword) != '') {

        $exactMatch = $filterParams['input_form']->exact_match == 1 ? 'match_phrase' : 'match';

        if (empty($filterParams['input_form']->search_in) || in_array(1,$filterParams['input_form']->search_in)) {
            $sparams['body']['query']['bool']['should'][][$exactMatch]['msg_visitor'] = $filterParams['input_form']->keyword;
            $sparams['body']['query']['bool']['should'][][$exactMatch]['msg_operator'] = $filterParams['input_form']->keyword;
            $sparams['body']['query']['bool']['should'][][$exactMatch]['msg_system'] = $filterParams['input_form']->keyword;
        } else {
            if (in_array(2,$filterParams['input_form']->search_in)) {
                $sparams['body']['query']['bool']['should'][][$exactMatch]['msg_visitor'] = $filterParams['input_form']->keyword;
            }
            
            if (in_array(3,$filterParams['input_form']->search_in)) {
                $sparams['body']['query']['bool']['should'][][$exactMatch]['msg_operator'] = $filterParams['input_form']->keyword;
            }
            
            if (in_array(4,$filterParams['input_form']->search_in)) {
                $sparams['body']['query']['bool']['should'][][$exactMatch]['msg_system'] = $filterParams['input_form']->keyword;
            }
        }

        $sparams['body']['query']['bool']['minimum_should_match'] = 1; // Minimum one condition should be matched

        $sparams['body']['highlight']['order'] = 'score';
        $sparams['body']['highlight']['fragment_size'] = 40;
        $sparams['body']['highlight']['number_of_fragments'] = 1;
        $sparams['body']['highlight']['fields']['msg_operator'] = new stdClass();
        $sparams['body']['highlight']['fields']['msg_visitor'] = new stdClass();
        $sparams['body']['highlight']['fields']['msg_system'] = new stdClass();
    }

    erLhcoreClassChatEventDispatcher::getInstance()->dispatch('elasticsearch.chatsearchexecute',array('sparams' => & $sparams, 'filter' => $filterParams));

    if ($filterParams['input_form']->sort_chat == 'asc') {
        $sort = array('time' => array('order' => 'asc'));
    } elseif ($filterParams['input_form']->sort_chat == 'relevance') {
        $sort = array('_score' => array('order' => 'desc'));
    } else {
        $sort = array('time' => array('order' => 'desc'));
    }

    $append = erLhcoreClassSearchHandler::getURLAppendFromInput($filterParams['input_form']);

    if ($filterParams['input_form']->ds == 1)
    {
        $total = erLhcoreClassModelESChat::getCount($sparams, array('date_index' => $dateFilter));
        $tpl->set('total_literal',$total);

        $pages = new lhPaginator();
        $pages->serverURL = erLhcoreClassDesign::baseurl('elasticsearch/list') . $append;
        $pages->items_total = $total > 9000 ? 9000 : $total;
        $pages->setItemsPerPage(30);
        $pages->paginate();

        if ($pages->items_total > 0) {
            $chats = erLhcoreClassModelESChat::getList(array(
                'offset' => $pages->low,
                'limit' => $pages->items_per_page,
                'body' => array_merge(array(
                    'sort' => $sort
                ), $sparams['body'])
            ),
                array('date_index' => $dateFilter));

            $chatIds = array();
            foreach ($chats as $prevChat) {
                $chatIds[$prevChat->chat_id] = array();
            }
            erLhcoreClassChatArcive::setArchiveAttribute($chatIds);
            $tpl->set('itemsArchive', $chatIds);
            $tpl->set('items', $chats);
        }

        $tpl->set('pages', $pages);
    }

} else {
    
    $sparams = array(
        'body' => array()
    );
    
    if ($filterParamsMsg['input_form']->message_text != '') {
        $sparams['body']['query']['bool']['must'][]['match']['msg'] = $filterParamsMsg['input_form']->message_text;
    }

    if ($filterParamsMsg['input_form']->ds == 1)
    {
        $append = erLhcoreClassSearchHandler::getURLAppendFromInput($filterParamsMsg['input_form']);

        $total = erLhcoreClassModelESMsg::getCount($sparams);
        $tpl->set('total_literal',$total);

        $pages = new lhPaginator();
        $pages->serverURL = erLhcoreClassDesign::baseurl('elasticsearch/list') .'/(tab)/messages' . $append;
        $pages->items_total = $total > 9000 ? 9000 : $total;
        $pages->setItemsPerPage(30);
        $pages->paginate();

        if ($filterParamsMsg['input_form']->sort_msg == 'asc') {
            $sort = array('time' => array('order' => 'asc'));
        } elseif ($filterParamsMsg['input_form']->sort_msg == 'desc'){
            $sort = array('time' => array('order' => 'desc'));
        } else {
            $sort = array('_score' => array('order' => 'desc'));
        }

        if ($pages->items_total > 0) {
            $tpl->set('items', erLhcoreClassModelESMsg::getList(array(
                'offset' => $pages->low,
                'limit' => $pages->items_per_page,
                'body' => array_merge(array(
                    'sort' => $sort
                ), $sparams['body'])
            )));
        }

        $tpl->set('pages', $pages);
    }
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
