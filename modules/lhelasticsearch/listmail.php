<?php

$tpl = erLhcoreClassTemplate::getInstance('elasticsearch/listmail.tpl.php');

// Chats filter
$filterParams = erLhcoreClassSearchHandler::getParams(array(
    'customfilterfile' => 'extension/elasticsearch/classes/filter/mail_list.php',
    'format_filter' => true,
    'use_override' => true,
    'uparams' => $Params['user_parameters_unordered']
));

if (isset($_GET['ds'])) {
    $filterParams['is_search'] = true;
} else {
    $filterParams['is_search'] = false;
}

$tpl->set('input', $filterParams['input_form']);

$sparams = array(
    'body' => array()
);

$dateFilter = array();

if (trim($filterParams['input_form']->conversation_id) != '') {
    $chat_ids = explode(',',trim($filterParams['input_form']->conversation_id));
    erLhcoreClassChat::validateFilterIn($chat_ids);

    // Merged id's support
    // In the future once we have archiving this part has to support archives
    $chat_ids = array_filter($chat_ids);

    if (!empty($chat_ids)) {
        $idsRelated = array_unique(erLhcoreClassModelMailconvMessage::getCount(['filter' => ['conversation_id_old' => $chat_ids]], '', false, 'conversation_id', false, true, true));
        if (!empty($idsRelated)) {
            $chat_ids = array_merge($chat_ids,$idsRelated);
        }
    }

    if (!empty($chat_ids)) {
        $sparams['body']['query']['bool']['must'][]['terms']['conversation_id'] = $chat_ids;
    }
}

if (is_numeric($filterParams['input_form']->has_attachment)) {
    if ($filterParams['input_form']->has_attachment == erLhcoreClassModelMailconvConversation::ATTACHMENT_MIX) {
        $sparams['body']['query']['bool']['must'][]['terms']['has_attachment_conv'] = [
            erLhcoreClassModelMailconvConversation::ATTACHMENT_INLINE,
            erLhcoreClassModelMailconvConversation::ATTACHMENT_FILE,
            erLhcoreClassModelMailconvConversation::ATTACHMENT_MIX
        ];
    } else if ($filterParams['input_form']->has_attachment == erLhcoreClassModelMailconvConversation::ATTACHMENT_INLINE) {
        $sparams['body']['query']['bool']['must'][]['terms']['has_attachment_conv'] = [
            erLhcoreClassModelMailconvConversation::ATTACHMENT_INLINE,
            erLhcoreClassModelMailconvConversation::ATTACHMENT_MIX
        ];
    } else if ($filterParams['input_form']->has_attachment == erLhcoreClassModelMailconvConversation::ATTACHMENT_FILE) {
        $sparams['body']['query']['bool']['must'][]['terms']['has_attachment_conv'] = [
            erLhcoreClassModelMailconvConversation::ATTACHMENT_FILE,
            erLhcoreClassModelMailconvConversation::ATTACHMENT_MIX
        ];
    } else if ($filterParams['input_form']->has_attachment == erLhcoreClassModelMailconvConversation::ATTACHMENT_EMPTY) {
        $sparams['body']['query']['bool']['must'][]['term']['has_attachment_conv'] = erLhcoreClassModelMailconvConversation::ATTACHMENT_EMPTY;
    } else if ($filterParams['input_form']->has_attachment == 5) { // No attachment (inline)
        $sparams['body']['query']['bool']['must_not'][]['terms']['has_attachment_conv'] = [
            erLhcoreClassModelMailconvConversation::ATTACHMENT_INLINE,
            erLhcoreClassModelMailconvConversation::ATTACHMENT_MIX
        ];
    } else if ($filterParams['input_form']->has_attachment == 4) { // No attachment (as file)
        $sparams['body']['query']['bool']['must_not'][]['terms']['has_attachment_conv'] = [
            erLhcoreClassModelMailconvConversation::ATTACHMENT_FILE,
            erLhcoreClassModelMailconvConversation::ATTACHMENT_MIX
        ];
    }
}

if (trim((string)$filterParams['input_form']->message_id) != '') {
    $sparams['body']['query']['bool']['must'][]['term']['id'] = (int)trim($filterParams['input_form']->message_id);
}

if ($filterParams['input_form']->from_name != '') {
    $sparams['body']['query']['bool']['must'][]['match']['from_name'] = $filterParams['input_form']->from_name;
}

if ($filterParams['input_form']->phone != '') {
    $sparams['body']['query']['bool']['must'][]['term']['phone'] = $filterParams['input_form']->phone;
}

if ($filterParams['input_form']->email != '') {
    if (empty($filterParams['input_form']->search_email_in) || $filterParams['input_form']->search_email_in == 1) {
        $sparams['body']['query']['bool']['must'][]['term']['customer_address_clean'] = erLhcoreClassElasticSearchIndex::cleanEmail(trim($filterParams['input_form']->email));
    } elseif ($filterParams['input_form']->search_email_in == 2) {
        $sparams['body']['query']['bool']['must'][]['term']['from_address_clean'] = erLhcoreClassElasticSearchIndex::cleanEmail(trim($filterParams['input_form']->email));
    } elseif ($filterParams['input_form']->search_email_in == 3) {
        $sparams['body']['query']['bool']['must'][]['term']['from_address'] = trim($filterParams['input_form']->email);
    } elseif ($filterParams['input_form']->search_email_in == 4) {
        $sparams['body']['query']['bool']['must'][]['term']['customer_address'] = trim($filterParams['input_form']->email);
    }
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

if (trim((string)$filterParams['input_form']->user_id) != '') {
    $sparams['body']['query']['bool']['must'][]['term']['conv_user_id'] = (int)trim($filterParams['input_form']->user_id);
}

if (trim((string)$filterParams['input_form']->response_type) != '') {
    $sparams['body']['query']['bool']['must'][]['term']['response_type'] = (int)trim($filterParams['input_form']->response_type);
}

if (trim((string)$filterParams['input_form']->status) != '') {
    $sparams['body']['query']['bool']['must'][]['term']['status'] = (int)trim($filterParams['input_form']->status);
}

if (trim((string)$filterParams['input_form']->opened) != '') {
    if ($filterParams['input_form']->opened === 0) {
        $sparams['body']['query']['bool']['must_not'][]['range']['opened_at']['gte'] = 1;
    } else {
        $sparams['body']['query']['bool']['must'][]['range']['opened_at']['gte'] = 1;
    }
}

if (trim((string)$filterParams['input_form']->status_conv) != '') {
    $sparams['body']['query']['bool']['must'][]['term']['status_conv'] = (int)trim($filterParams['input_form']->status_conv);
}



if (is_numeric($filterParams['input_form']->is_external)) {
    $sparams['body']['query']['bool']['must'][]['term']['is_external'] = $filterParams['input_form']->is_external;
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

$depIdFilter = [];

if (trim((string)$filterParams['input_form']->department_id) != '') {
    $depIdFilter[] = (int)trim($filterParams['input_form']->department_id);
}

if (trim((string)$filterParams['input_form']->department_group_id) != '') {
    $db = ezcDbInstance::get();
    $stmt = $db->prepare('SELECT dep_id FROM lh_departament_group_member WHERE dep_group_id = :group_id');
    $stmt->bindValue( ':group_id', $filterParams['input']->department_group_id, PDO::PARAM_INT);
    $stmt->execute();
    $depIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (!empty($depIds)) {
        $depIdFilter = array_merge($depIdFilter, $depIds);
    }
}

if (isset($filterParams['input']->department_group_ids) && is_array($filterParams['input']->department_group_ids) && !empty($filterParams['input']->department_group_ids)) {
    erLhcoreClassChat::validateFilterIn($filterParams['input']->department_group_ids);
    $db = ezcDbInstance::get();
    $stmt = $db->prepare('SELECT dep_id FROM lh_departament_group_member WHERE dep_group_id IN (' . implode(',',$filterParams['input']->department_group_ids) . ')');
    $stmt->execute();
    $depIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (!empty($depIds)) {
        $depIdFilter = array_merge($depIdFilter, $depIds);
    }
}

if (isset($filterParams['input']->department_ids) && is_array($filterParams['input']->department_ids) && !empty($filterParams['input']->department_ids)) {
    erLhcoreClassChat::validateFilterIn($filterParams['input']->department_ids);
    $depIdFilter = array_merge($depIdFilter, $filterParams['input']->department_ids);
}

if (!empty($depIdFilter)) {
    $sparams['body']['query']['bool']['must'][]['terms']['dep_id'] = $depIdFilter;
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

if (isset($filterParams['input']->ids) && is_array($filterParams['input']->ids) && !empty($filterParams['input']->ids)) {
    erLhcoreClassChat::validateFilterInString($filterParams['input']->ids);
    if (!empty($filterParams['input']->ids)) {
        $sparams['body']['query']['bool']['must'][]['terms']['conversation_id'] = $filterParams['input']->ids;
    }
}

if (isset($filterParams['input']->status_conv_id) && is_array($filterParams['input']->status_conv_id) && !empty($filterParams['input']->status_conv_id)) {
    erLhcoreClassChat::validateFilterInString($filterParams['input']->status_conv_id);
    if (!empty($filterParams['input']->status_conv_id)) {
        $sparams['body']['query']['bool']['must'][]['terms']['status_conv'] = $filterParams['input']->status_conv_id;
    }
}

if (isset($filterParams['input']->status_msg_id) && is_array($filterParams['input']->status_msg_id) && !empty($filterParams['input']->status_msg_id)) {
    erLhcoreClassChat::validateFilterInString($filterParams['input']->status_msg_id);
    if (!empty($filterParams['input']->status_msg_id)) {
        $sparams['body']['query']['bool']['must'][]['terms']['status'] = $filterParams['input']->status_msg_id;
    }
}

if (isset($filterParams['input']->mailbox_ids) && is_array($filterParams['input']->mailbox_ids) && !empty($filterParams['input']->mailbox_ids)) {
    erLhcoreClassChat::validateFilterInString($filterParams['input']->mailbox_ids);
    if (!empty($filterParams['input']->mailbox_ids)) {
        $sparams['body']['query']['bool']['must'][]['terms']['mailbox_id'] = $filterParams['input']->mailbox_ids;
    }
}

if ($filterParams['input_form']->hvf == 1) {
    $sparams['body']['query']['bool']['must'][]['terms']['has_attachment'] = [1,2,3];
}

if (trim($filterParams['input_form']->keyword) != '') {

    $exactMatch = $filterParams['input_form']->exact_match == 1 ? 'match_phrase' : 'match';

    $paramQuery = [
        'query' => $filterParams['input_form']->keyword
    ];

    if ($filterParams['input_form']->fuzzy == 1 && $filterParams['input_form']->exact_match != 1 && $filterParams['input_form']->expression != 1) {
        $paramQuery['fuzziness'] = 'AUTO';
        $paramQuery['prefix_length'] = max((mb_strlen($filterParams['input_form']->keyword) - (is_numeric($filterParams['input_form']->fuzzy_prefix) ? $filterParams['input_form']->fuzzy_prefix : 1)),0);
    }

    if (empty($filterParams['input_form']->search_in)) {
        if ($filterParams['input_form']->expression == 1) {
            $queryStringParam = $paramQuery;
            $sparams['body']['query']['bool']['must'][]["query_string"] = $queryStringParam;
        } else {
            $sparams['body']['query']['bool']['should'][][$exactMatch]['subject'] = $paramQuery;
            $sparams['body']['query']['bool']['should'][][$exactMatch]['alt_body'] = $paramQuery;
        }
    } else {
        if (in_array(1,$filterParams['input_form']->search_in)) {
            $sparams['body']['query']['bool']['should'][][$exactMatch]['subject'] = $paramQuery;
        }

        if (in_array(2,$filterParams['input_form']->search_in)) {
            $sparams['body']['query']['bool']['should'][][$exactMatch]['alt_body'] = $paramQuery;
        }

        if (in_array(3,$filterParams['input_form']->search_in)) {
            $sparams['body']['query']['bool']['should'][][$exactMatch]['from_name'] = $paramQuery;
        }

        if (in_array(4,$filterParams['input_form']->search_in)) {
            $sparams['body']['query']['bool']['should'][][$exactMatch]['sender_name'] = $paramQuery;
        }

        if (in_array(5,$filterParams['input_form']->search_in)) {
            $sparams['body']['query']['bool']['should'][][$exactMatch]['delivery_status'] = $paramQuery;
        }

        if (in_array(6,$filterParams['input_form']->search_in)) {
            $sparams['body']['query']['bool']['should'][][$exactMatch]['rfc822_body'] = $paramQuery;
        }

        if (in_array(7,$filterParams['input_form']->search_in)) {
            $sparams['body']['query']['bool']['should'][][$exactMatch]['reply_to_data'] = $paramQuery;
        }

        if (in_array(8,$filterParams['input_form']->search_in)) {
            $sparams['body']['query']['bool']['should'][][$exactMatch]['to_data'] = $paramQuery;
        }

        if (in_array(9,$filterParams['input_form']->search_in)) {
            $sparams['body']['query']['bool']['should'][][$exactMatch]['cc_data'] = $paramQuery;
        }

        if (in_array(10,$filterParams['input_form']->search_in)) {
            $sparams['body']['query']['bool']['should'][][$exactMatch]['bcc_data'] = $paramQuery;
        }

        if (in_array(11,$filterParams['input_form']->search_in)) {
            $sparams['body']['query']['bool']['should'][][$exactMatch]['mb_folder'] = $paramQuery;
        }

        if (in_array(12,$filterParams['input_form']->search_in)) {
            $sparams['body']['query']['bool']['should'][][$exactMatch]['customer_name'] = $paramQuery;
        }
    }

    if ($filterParams['input_form']->expression != 1) {
        $sparams['body']['query']['bool']['minimum_should_match'] = 1; // Minimum one condition should be matched
    }


    $sparams['body']['highlight']['order'] = 'score';
    $sparams['body']['highlight']['fragment_size'] = 40;
    $sparams['body']['highlight']['number_of_fragments'] = 5;

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

$append = erLhcoreClassSearchHandler::getURLAppendFromInput($filterParams['input_form'],false,[],['keyword']);

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
        $append = erLhcoreClassSearchHandler::getURLAppendFromInput($filterParams['input_form'],false,[],['keyword']);
        $tpl->set('action_url', erLhcoreClassDesign::baseurl('elasticsearch/listmail') . $append['append']);
        $tpl->set('query_url', $append['query']);
        $tpl->set('input', $filterParams['input_form']);
        
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

    if (in_array($Params['user_parameters_unordered']['export'], array(1))) {
        if (ezcInputForm::hasPostData()) {
            session_write_close();
            @ini_set('max_execution_time', '300');

            $chatIds = [];
            $continue = true;
            $gte = 0;
            $sparamsOriginal = $sparams['body'];

            while ($continue) {
                $sparamsOriginalBatch = $sparamsOriginal;

                    if ($sort['conversation_id']['order'] == 'desc') {
                        $sortAttribute = 'conversation_id';
                        if ($gte > 0) {
                            $sparamsOriginalBatch['query']['bool']['must'][]['range']['conversation_id']['lte'] = $gte;
                        }
                    } elseif ($sort['conversation_id']['order'] == 'asc') {
                        $sortAttribute = 'conversation_id';
                        if ($gte > 0) {
                            $sparamsOriginalBatch['query']['bool']['must'][]['range']['conversation_id']['gte'] = $gte;
                        }
                    } elseif ($sort['time']['order'] == 'desc') {
                        $sortAttribute = 'time';
                        if ($gte > 0) {
                            $sparamsOriginalBatch['query']['bool']['must'][]['range']['time']['lte'] = $gte;
                        }
                    } elseif ($sort['time']['order'] == 'asc') {
                        if ($gte > 0) {
                            $sparamsOriginalBatch['query']['bool']['must'][]['range']['time']['gte'] = $gte;
                        }
                        $sortAttribute = 'time';
                    } else {
                        $sort = ['conversation_id' => ['order' => 'desc']];
                        if ($gte > 0) {
                            $sparamsOriginalBatch['query']['bool']['must'][]['range']['conversation_id']['lte'] = $gte;
                        }
                        $sortAttribute = 'conversation_id';
                    }

                $chats = erLhcoreClassModelESMail::getList(array(
                    'offset' => 0,
                    'limit' => 1000,
                    'body' => array_merge(array(
                        'sort' => $sort
                    ), $sparamsOriginalBatch)
                ),
                    array('date_index' => $dateFilter));

                foreach ($chats as $chatID) {
                    $chatIds[] = $chatID->conversation_id;
                    $gte = $chatID->{$sortAttribute};
                }

                if (count($chats) == 0 || count($chats) < 1000) {
                    $continue = false;
                }
            }

            $chatIds = array_unique($chatIds);

            if (!$currentUser->hasAccessTo('lhaudit','ignore_view_actions')) {
                erLhcoreClassLog::write(erLhcoreClassSearchHandler::getURLAppendFromInput($filterParams['input_form']),
                    ezcLog::SUCCESS_AUDIT,
                    array(
                        'source' => 'lhc',
                        'category' => 'mail_export_elastic',
                        'line' => __LINE__,
                        'file' => __FILE__,
                        'object_id' => 0,
                        'user_id' => $currentUser->getUserID()
                    )
                );
            }

            erLhcoreClassMailconvExport::export(null, array('conversation_id' => $chatIds, 'csv' => isset($_POST['CSV']), 'type' => (isset($_POST['exportOptions']) ? $_POST['exportOptions'] : [])));

            exit;
        } else {
            $tpl = erLhcoreClassTemplate::getInstance('lhmailconv/export_config.tpl.php');
            $append = erLhcoreClassSearchHandler::getURLAppendFromInput($filterParams['input_form'],false,[],['keyword']);
            $tpl->set('action_url', erLhcoreClassDesign::baseurl('elasticsearch/listmail') . $append['append']);
            $tpl->set('query_url', $append['query']);
            echo $tpl->fetch();
            exit;
        }
    }

    // Archive actions
    if (isset($Params['user_parameters_unordered']['export']) && $Params['user_parameters_unordered']['export'] == 5) {
        $tpl = erLhcoreClassTemplate::getInstance('elasticsearch/lhmailconv/delete_conversations_archive.tpl.php');
        $append = erLhcoreClassSearchHandler::getURLAppendFromInput($filterParams['input_form'],false,[],['keyword']);
        $tpl->set('action_url', erLhcoreClassDesign::baseurl('elasticsearch/listmail') . $append['append']);
        $tpl->set('query_url', $append['query']);
        if (ezcInputForm::hasPostData() && isset($_GET['archive_id'])) {
            session_write_close();

            erLhcoreClassRestAPIHandler::setHeaders();

            if (!isset($_SERVER['HTTP_X_CSRFTOKEN']) || !$currentUser->validateCSFRToken($_SERVER['HTTP_X_CSRFTOKEN'])) {
                echo json_encode(['left_to_delete' => 0, 'error' => 'Token']);
                exit;
            }

            $archive = \LiveHelperChat\Models\mailConv\Archive\Range::fetch($_GET['archive_id']);

            if (is_object($archive) && $archive->type == \LiveHelperChat\Models\mailConv\Archive\Range::ARCHIVE_TYPE_BACKUP) {

                if (isset($_POST['schedule'])) {
                    $deleteFilter = new \LiveHelperChatExtension\elasticsearch\providers\Delete\DeleteFilter();
                    $deleteFilter->user_id = $currentUser->getUserID();
                    $deleteFilter->filter = json_encode(['date_index' => $dateFilter, 'filter' => array_merge(array('sort' => $sort), $sparams['body'])]);
                    $deleteFilter->filter_input = json_encode($filterParams['input_form']);
                    $deleteFilter->archive_id = $archive->id;
                    $deleteFilter->delete_policy = (isset($_POST['delete_policy']) && $_POST['delete_policy'] === 'true') ? 0 : 1;
                    $deleteFilter->saveThis();

                    echo json_encode(['result' => erTranslationClassLhTranslation::getInstance()->getTranslation('chatarchive/list','Scheduled delete flow with ID') . ' - ' . $deleteFilter->id]);
                    exit;

                } else {

                    $chatsElastic = erLhcoreClassModelESMail::getList(array(
                        'offset' => 0,
                        'limit' => 20,
                        'body' => array_merge(array(
                            'sort' => $sort
                        ), $sparams['body'])
                    ), array('date_index' => $dateFilter));

                    $chatsDatabaseIds = [];
                    foreach ($chatsElastic as $chatElastic) {
                        $chatsDatabaseIds[] = $chatElastic->conversation_id;
                    }

                    if (!empty($chatsElastic)) {
                        $items = erLhcoreClassModelMailconvConversation::getList(['filterin' => ['id' => $chatsDatabaseIds]]);
                    } else {
                        echo json_encode(['left_to_delete' => 0]);
                        exit;
                    }

                    if (count($items) > 0) {
                        $archive->process($items, ['ignore_imap' => !(isset($_POST['delete_policy']) && $_POST['delete_policy'] === 'true')]);
                        // Delete elastic insantly
                        foreach ($chatsElastic as $itemElastic) {
                            try {
                                $itemElastic->removeThis();
                            } catch (Exception $e) {

                            }
                        }
                        echo json_encode(['left_to_delete' => count($chatsElastic)]);
                        exit;
                    } else {
                        // Delete elastic insantly
                        foreach ($chatsElastic as $itemElastic) {
                            try {
                                $itemElastic->removeThis();
                            } catch (Exception $e) {

                            }
                        }
                        echo json_encode(['left_to_delete' => count($chatsElastic), 'waiting_elastic_search' => true]);
                        exit;
                    }
                }

            } else {
                echo json_encode(['left_to_delete' => 0]);
                exit;
            }
        }

        $tpl->set('update_records',erLhcoreClassModelESMail::getCount($sparams, array('date_index' => $dateFilter)));

        echo $tpl->fetch();
        exit;
    }

    if (isset($Params['user_parameters_unordered']['export']) && $Params['user_parameters_unordered']['export'] == 4) {
        $tpl = erLhcoreClassTemplate::getInstance('elasticsearch/lhmailconv/delete_conversations.tpl.php');
        $append = erLhcoreClassSearchHandler::getURLAppendFromInput($filterParams['input_form'],false,[],['keyword']);
        $tpl->set('action_url', erLhcoreClassDesign::baseurl('elasticsearch/listmail') . $append['append']);
        $tpl->set('query_url', $append['query']);
        if (ezcInputForm::hasPostData()) {

            erLhcoreClassRestAPIHandler::setHeaders();

            if (!isset($_SERVER['HTTP_X_CSRFTOKEN']) || !$currentUser->validateCSFRToken($_SERVER['HTTP_X_CSRFTOKEN'])) {
                echo json_encode(['left_to_delete' => 0, 'error' => 'Token']);
                exit;
            }

            if (isset($_POST['schedule'])) {
                $deleteFilter = new \LiveHelperChatExtension\elasticsearch\providers\Delete\DeleteFilter();
                $deleteFilter->user_id = $currentUser->getUserID();
                $deleteFilter->filter = json_encode(['date_index' => $dateFilter, 'filter' => array_merge(array('sort' => $sort), $sparams['body'])]);
                $deleteFilter->filter_input = json_encode($filterParams['input_form']);
                $deleteFilter->delete_policy = (isset($_POST['delete_policy']) && $_POST['delete_policy'] === 'true') ? 0 : 1;
                $deleteFilter->saveThis();
                echo json_encode(['result' => erTranslationClassLhTranslation::getInstance()->getTranslation('chatarchive/list','Scheduled delete flow with ID') . ' - ' . $deleteFilter->id]);
                exit;
            } else {
                session_write_close();

                $chatsElastic = erLhcoreClassModelESMail::getList(array(
                    'offset' => 0,
                    'limit' => 20,
                    'body' => array_merge(array(
                        'sort' => $sort
                    ), $sparams['body'])
                ),array('date_index' => $dateFilter));

                $chatsDatabaseIds = [];
                foreach ($chatsElastic as $chatElastic) {
                    $chatsDatabaseIds[] = $chatElastic->conversation_id;
                }

                if (!empty($chatsDatabaseIds)) {
                    $mails = erLhcoreClassModelMailconvConversation::getList(array('filterin' => array('id' => $chatsDatabaseIds)));
                    foreach ($chatsDatabaseIds as $conversationId) {
                        if (isset($mails[$conversationId])) {
                            $mails[$conversationId]->ignore_imap = (isset($_POST['delete_policy']) && $_POST['delete_policy'] === 'true') ? false : true;
                            $mails[$conversationId]->removeThis();
                        } else {
                            $mailData = \LiveHelperChat\mailConv\Archive\Archive::fetchMailById($conversationId);
                            if (isset($mailData['mail'])) {
                                $mailData['mail']->ignore_imap = (isset($_POST['delete_policy']) && $_POST['delete_policy'] === 'true') ? false : true;
                                $mailData['mail']->removeThis();
                            }
                        }
                    }

                    // Remove Elastic records directly
                    foreach ($chatsElastic as $mailElastic) {
                        try {
                            $mailElastic->removeThis();
                        } catch (Exception $e) {
                            // Ignore
                        }
                    }

                    echo json_encode(['left_to_delete' => count($chatsElastic)]);
                    exit;

                } else {
                    echo json_encode(['left_to_delete' => 0]);
                    exit;
                }
            }
        }

        $tpl->set('update_records',erLhcoreClassModelESMail::getCount($sparams, array('date_index' => $dateFilter)));
        echo $tpl->fetch();
        exit;
    }

    if ($currentUser->hasAccessTo('lhelasticsearch','delete') && isset($_POST['doDelete'])) {
        if (!isset($_POST['csfr_token']) || !$currentUser->validateCSFRToken($_POST['csfr_token'])) {
            erLhcoreClassModule::redirect('elasticsearch/listmail');
            exit;
        }

        $definition = array(
            'ConversationID' => new ezcInputFormDefinitionElement(
                ezcInputFormDefinitionElement::OPTIONAL, 'int', null, FILTER_REQUIRE_ARRAY
            ),
        );

        $form = new ezcInputForm( INPUT_POST, $definition );
        $Errors = array();

        if ( $form->hasValidData( 'ConversationID' ) && !empty($form->ConversationID) ) {
            $mails = erLhcoreClassModelMailconvConversation::getList(array('filterin' => array('id' => $form->ConversationID)));
            foreach ($form->ConversationID as $conversationId) {
                if (isset($mails[$conversationId])) {
                    $mails[$conversationId]->removeThis();
                } else {
                    $mailData = \LiveHelperChat\mailConv\Archive\Archive::fetchMailById($conversationId);
                    if (isset($mailData['mail'])) {
                        $mailData['mail']->removeThis();
                    }
                }
            }

            $sparamsDelete = $sparams;
            $sparamsDelete['body']['query']['bool']['must'][]['terms']['conversation_id'] = $form->ConversationID;

            $chatsElastic = erLhcoreClassModelESMail::getList(array(
                'offset' => 0,
                'limit' => 1000,
                'body' => array_merge(array(
                    'sort' => $sort
                ), $sparamsDelete['body'])
            ),array('date_index' => $dateFilter));

            // Remove Elastic Records directly
            foreach ($chatsElastic as $mailElastic) {
                try {
                    $mailElastic->removeThis();
                } catch (Exception $e) {
                    // Ignore
                }
            }
        }

        $tpl->set('delete_processed',true);
    }


    $total = erLhcoreClassModelESMail::getCount($sparams, array('date_index' => $dateFilter));
    $tpl->set('total_literal',$total);

    $pages = new lhPaginator();
    $pages->serverURL = erLhcoreClassDesign::baseurl('elasticsearch/listmail') . $append['append'];
    $pages->querystring = $append['query'];
    $pages->items_total = $total > 9000 ? 9000 : $total;
    if ($filterParams['input']->ipp > 0) {
        $pages->setItemsPerPage($filterParams['input']->ipp);
    } else {
        $pages->setItemsPerPage(60);
    }
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

        $iconsAdditional = erLhAbstractModelChatColumn::getList(array('ignore_fields' => array('position','conditions','column_identifier','enabled'), 'sort' => false, 'filter' => array('icon_mode' => 1, 'enabled' => 1, 'mail_enabled' => 1)));
        $iconsAdditionalColumn = erLhAbstractModelChatColumn::getList(array('ignore_fields' => array('position','conditions','column_identifier','enabled'), 'sort' => 'position ASC, id ASC','filter' => array('enabled' => 1, 'icon_mode' => 0, 'mail_list_enabled' => 1)));

        erLhcoreClassChat::prefillGetAttributes($chats, array(), array(), array('additional_columns' => ($iconsAdditional + $iconsAdditionalColumn), 'do_not_clean' => true));

        $tpl->set('icons_additional',$iconsAdditional);
        $tpl->set('additional_chat_columns',$iconsAdditionalColumn);

        $tpl->set('items', $chats);
    }

    $tpl->set('pages', $pages);

    if (!$currentUser->hasAccessTo('lhaudit','ignore_view_actions')) {
        erLhcoreClassLog::write(erLhcoreClassSearchHandler::getURLAppendFromInput($filterParams['input_form']),
            ezcLog::SUCCESS_AUDIT,
            array(
                'source' => 'lhc',
                'category' => 'mail_search_elastic',
                'line' => __LINE__,
                'file' => __FILE__,
                'object_id' => 0,
                'user_id' => $currentUser->getUserID()
            )
        );
    }

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
