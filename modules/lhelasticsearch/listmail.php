<?php

$tpl = erLhcoreClassTemplate::getInstance('elasticsearch/listmail.tpl.php');

// Chats filter
if (isset($_GET['ds'])) {
    $filterParams = erLhcoreClassSearchHandler::getParams(array(
        'customfilterfile' => 'extension/elasticsearch/classes/filter/mail_list.php',
        'format_filter' => true,
        'use_override' => true,
        'uparams' => $Params['user_parameters_unordered']
    ));
    $filterParams['is_search'] = true;
} else {
    $filterParams = erLhcoreClassSearchHandler::getParams(array(
        'customfilterfile' => 'extension/elasticsearch/classes/filter/mail_list.php',
        'format_filter' => true,
        'uparams' => $Params['user_parameters_unordered']
    ));
    $filterParams['is_search'] = false;
}

$tpl->set('input', $filterParams['input_form']);


$sparams = array(
    'body' => array()
);

$dateFilter = array();

if (trim($filterParams['input_form']->conversation_id) != '') {
    $sparams['body']['query']['bool']['must'][]['term']['conversation_id'] = (int)trim($filterParams['input_form']->conversation_id);
}

if (trim($filterParams['input_form']->message_id) != '') {
    $sparams['body']['query']['bool']['must'][]['term']['id'] = (int)trim($filterParams['input_form']->message_id);
}

if ($filterParams['input_form']->from_name != '') {
    $sparams['body']['query']['bool']['must'][]['match']['from_name'] = $filterParams['input_form']->from_name;
}

if ($filterParams['input_form']->phone != '') {
    $sparams['body']['query']['bool']['must'][]['term']['phone'] = $filterParams['input_form']->phone;
}

if ($filterParams['input_form']->email != '') {
    $sparams['body']['query']['bool']['must'][]['term']['from_address'] = trim($filterParams['input_form']->email);
}

if ($filterParams['input_form']->sender_host != '') {
    $sparams['body']['query']['bool']['must'][]['term']['sender_host'] = trim($filterParams['input_form']->sender_host);
}

if ($filterParams['input_form']->sender_address != '') {
    $sparams['body']['query']['bool']['must'][]['term']['sender_address'] = trim($filterParams['input_form']->sender_address);
}

if ($filterParams['input_form']->from_host != '') {
    $sparams['body']['query']['bool']['must'][]['term']['from_host'] = trim($filterParams['input_form']->from_host);
}

if (trim($filterParams['input_form']->user_id) != '') {
    $sparams['body']['query']['bool']['must'][]['term']['conv_user_id'] = (int)trim($filterParams['input_form']->user_id);
}

if (trim($filterParams['input_form']->response_type) != '') {
    $sparams['body']['query']['bool']['must'][]['term']['response_type'] = (int)trim($filterParams['input_form']->response_type);
}

if (trim($filterParams['input_form']->status) != '') {
    $sparams['body']['query']['bool']['must'][]['term']['status'] = (int)trim($filterParams['input_form']->status);
}

if (trim($filterParams['input_form']->opened) != '') {
    if ($filterParams['input_form']->opened === 0) {
        $sparams['body']['query']['bool']['must'][]['term']['opened_at'] = (int)0;
    } else {
        $sparams['body']['query']['bool']['must_not'][]['term']['opened_at'] = 0;
    }
}

if (trim($filterParams['input_form']->status_conv) != '') {
    $sparams['body']['query']['bool']['must'][]['term']['status_conv'] = (int)trim($filterParams['input_form']->status_conv);
}

if (trim($filterParams['input_form']->department_id) != '') {
    $sparams['body']['query']['bool']['must'][]['term']['dep_id'] = (int)trim($filterParams['input_form']->department_id);
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
        $sparams['body']['query']['bool']['must'][]['terms']['conv_user_id'] = $userIds;
    }
}

if (isset($filterParams['input']->group_ids) && is_array($filterParams['input']->group_ids) && !empty($filterParams['input']->group_ids)) {

    erLhcoreClassChat::validateFilterIn($filterParams['input']->group_ids);

    $db = ezcDbInstance::get();
    $stmt = $db->prepare('SELECT user_id FROM lh_groupuser WHERE group_id IN (' . implode(',',$filterParams['input']->group_ids) .')');
    $stmt->execute();
    $userIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (!empty($userIds)) {
        $sparams['body']['query']['bool']['must'][]['terms']['conv_user_id'] = $userIds;
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

if (isset($filterParams['input']->lang_ids) && is_array($filterParams['input']->lang_ids) && !empty($filterParams['input']->lang_ids)) {
    $sparams['body']['query']['bool']['must'][]['terms']['lang'] = $filterParams['input']->lang_ids;
}

if (isset($filterParams['input']->user_ids) && is_array($filterParams['input']->user_ids) && !empty($filterParams['input']->user_ids)) {
    erLhcoreClassChat::validateFilterIn($filterParams['input']->user_ids);
    $sparams['body']['query']['bool']['must'][]['terms']['conv_user_id'] = $filterParams['input']->user_ids;
}

if ($filterParams['input_form']->no_user == 1) {
    $sparams['body']['query']['bool']['must'][]['term']['conv_user_id'] = 0;
}

if ($filterParams['input_form']->undelivered == 1) {
    $sparams['body']['query']['bool']['must'][]['term']['undelivered'] = 1;
}

if (isset($filterParams['filter']['filtergte']['time'])) {
    $sparams['body']['query']['bool']['must'][]['range']['time']['gte'] = $filterParams['filter']['filtergte']['time'] * 1000;
    $dateFilter['gte'] = $filterParams['filter']['filtergte']['time'];
}

if (isset($filterParams['filter']['filterlte']['time'])) {
    $sparams['body']['query']['bool']['must'][]['range']['time']['lte'] = $filterParams['filter']['filterlte']['time'] * 1000;
    $dateFilter['lte'] = $filterParams['filter']['filterlte']['time'];
}

if ($filterParams['input_form']->has_operator == 1) {
    $sparams['body']['query']['bool']['must'][]['range']['conv_user_id']['gt'] = 0;
}

if ($filterParams['input_form']->is_followup == 1) {
    $sparams['body']['query']['bool']['must'][]['range']['follow_up_id']['gt'] = 0;
}

if (isset($filterParams['input']->subject_id) && is_array($filterParams['input']->subject_id) && !empty($filterParams['input']->subject_id)) {

    erLhcoreClassChat::validateFilterInString($filterParams['input']->subject_id);

    if (!empty($filterParams['input']->subject_id)) {
        $sparams['body']['query']['bool']['must'][]['terms']['subject_id'] = $filterParams['input']->subject_id;
    }
}

if ($filterParams['input_form']->hvf == 1) {
    $sparams['body']['query']['bool']['must'][]['terms']['has_attachment'] = [1,2,3];
}

if (trim($filterParams['input_form']->keyword) != '') {

    $exactMatch = $filterParams['input_form']->exact_match == 1 ? 'match_phrase' : 'match';

    if (empty($filterParams['input_form']->search_in)) {
        $sparams['body']['query']['bool']['should'][][$exactMatch]['subject'] = $filterParams['input_form']->keyword;
        $sparams['body']['query']['bool']['should'][][$exactMatch]['alt_body'] = $filterParams['input_form']->keyword;
    } else {
        if (in_array(1,$filterParams['input_form']->search_in)) {
            $sparams['body']['query']['bool']['should'][][$exactMatch]['subject'] = $filterParams['input_form']->keyword;
        }

        if (in_array(2,$filterParams['input_form']->search_in)) {
            $sparams['body']['query']['bool']['should'][][$exactMatch]['alt_body'] = $filterParams['input_form']->alt_body;
        }

        if (in_array(3,$filterParams['input_form']->search_in)) {
            $sparams['body']['query']['bool']['should'][][$exactMatch]['from_name'] = $filterParams['input_form']->keyword;
        }

        if (in_array(4,$filterParams['input_form']->search_in)) {
            $sparams['body']['query']['bool']['should'][][$exactMatch]['sender_name'] = $filterParams['input_form']->keyword;
        }

        if (in_array(5,$filterParams['input_form']->search_in)) {
            $sparams['body']['query']['bool']['should'][][$exactMatch]['delivery_status'] = $filterParams['input_form']->keyword;
        }

        if (in_array(6,$filterParams['input_form']->search_in)) {
            $sparams['body']['query']['bool']['should'][][$exactMatch]['rfc822_body'] = $filterParams['input_form']->keyword;
        }

        if (in_array(7,$filterParams['input_form']->search_in)) {
            $sparams['body']['query']['bool']['should'][][$exactMatch]['reply_to_data'] = $filterParams['input_form']->keyword;
        }

        if (in_array(8,$filterParams['input_form']->search_in)) {
            $sparams['body']['query']['bool']['should'][][$exactMatch]['to_data'] = $filterParams['input_form']->keyword;
        }

        if (in_array(9,$filterParams['input_form']->search_in)) {
            $sparams['body']['query']['bool']['should'][][$exactMatch]['cc_data'] = $filterParams['input_form']->keyword;
        }

        if (in_array(10,$filterParams['input_form']->search_in)) {
            $sparams['body']['query']['bool']['should'][][$exactMatch]['bcc_data'] = $filterParams['input_form']->keyword;
        }

        if (in_array(11,$filterParams['input_form']->search_in)) {
            $sparams['body']['query']['bool']['should'][][$exactMatch]['mb_folder'] = $filterParams['input_form']->keyword;
        }
    }

    $sparams['body']['query']['bool']['minimum_should_match'] = 1; // Minimum one condition should be matched

    $sparams['body']['highlight']['order'] = 'score';
    $sparams['body']['highlight']['fragment_size'] = 40;
    $sparams['body']['highlight']['number_of_fragments'] = 1;

    $sparams['body']['highlight']['fields']['subject'] = new stdClass();
    $sparams['body']['highlight']['fields']['alt_body'] = new stdClass();
}

erLhcoreClassChatEventDispatcher::getInstance()->dispatch('elasticsearch.mailsearchexecute',array('sparams' => & $sparams, 'filter' => $filterParams));

if ($filterParams['input_form']->sort_chat == 'asc') {
    $sort = array('conversation_id' => array('order' => 'asc'));
} elseif ($filterParams['input_form']->sort_chat == 'lastupdateasc') {
    $sort = array('time' => array('order' => 'asc'));
} elseif ($filterParams['input_form']->sort_chat == 'lastupdatedesc') {
    $sort = array('time' => array('order' => 'desc'));
} elseif ($filterParams['input_form']->sort_chat == 'relevance') {
    $sort = array('_score' => array('order' => 'desc'));
} else {
    $sort = array('conversation_id' => array('order' => 'desc'));
}

$append = erLhcoreClassSearchHandler::getURLAppendFromInput($filterParams['input_form']);

if ($filterParams['input_form']->ds == 1)
{
    if (isset($Params['user_parameters_unordered']['export']) && $Params['user_parameters_unordered']['export'] == 2) {

        $savedSearch = new erLhAbstractModelSavedSearch();

        if ($Params['user_parameters_unordered']['view'] > 0) {
            $savedSearchPresent = erLhAbstractModelSavedSearch::fetch($Params['user_parameters_unordered']['view']);
            if ($savedSearchPresent->user_id == $currentUser->getUserID()) {
                $savedSearch = $savedSearchPresent;
            }
        }

        $tpl = erLhcoreClassTemplate::getInstance('lhviews/save_chat_view.tpl.php');
        $tpl->set('action_url', erLhcoreClassDesign::baseurl('elasticsearch/listmail') . erLhcoreClassSearchHandler::getURLAppendFromInput($filterParams['input_form']));
        if (ezcInputForm::hasPostData()) {
            $Errors = erLhcoreClassAdminChatValidatorHelper::validateSavedSearch($savedSearch, array(
                'sort' => $sort,
                'sparams' => $sparams,
                'filter' => $filterParams['filter'],
                'input_form' => $filterParams['input_form']
            ));
            if (empty($Errors)) {
                $savedSearch->user_id = $currentUser->getUserID();
                $savedSearch->scope = 'esmail';
                $savedSearch->saveThis();
                $tpl->set('updated', true);
            } else {
                $tpl->set('errors', $Errors);
            }
        }
        $tpl->set('item', $savedSearch);
        echo $tpl->fetch();
        exit;
    }



    $total = erLhcoreClassModelESMail::getCount($sparams, array('date_index' => $dateFilter));
    $tpl->set('total_literal',$total);

    $pages = new lhPaginator();
    $pages->serverURL = erLhcoreClassDesign::baseurl('elasticsearch/listmail') . $append;
    $pages->items_total = $total > 9000 ? 9000 : $total;
    $pages->setItemsPerPage(30);
    $pages->paginate();

    if ($pages->items_total > 0) {

        // @todo Switch to aggregation
        // $sparams['body']['aggs']['unique_ids']['terms']['field'] = 'conversation_id';

        $chats = erLhcoreClassModelESMail::getList(array(
            'offset' => $pages->low,
            'limit' => $pages->items_per_page,
            'body' => array_merge(array(
                'sort' => $sort
            ), $sparams['body'])
        ),
            array('date_index' => $dateFilter));

        $previousConversation = null;
        foreach ($chats as $prevChat) {
            if (is_object($previousConversation) && $previousConversation->conversation_id == $prevChat->conversation_id) {
                $previousConversation->has_many_messages = true;
            }
            $previousConversation = $prevChat;
        }

        $tpl->set('items', $chats);
    }

    $tpl->set('pages', $pages);
}

$tpl->set('Result',['path' => array(
    array(
        'url' => erLhcoreClassDesign::baseurl('elasticsearch/index'),
        'title' => erTranslationClassLhTranslation::getInstance()->getTranslation('lhelasticsearch/module', 'Elastic Search')
    ),
    array(
        'url' => erLhcoreClassDesign::baseurl('elasticsearch/list'),
        'title' => erTranslationClassLhTranslation::getInstance()->getTranslation('lhelasticsearch/list', 'Mails list')
    )
)]);

$Result['body_class'] = 'h-100 dashboard-height';
$Result['content'] = $tpl->fetch();
