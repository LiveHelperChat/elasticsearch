<?php
$tpl = erLhcoreClassTemplate::getInstance('elasticsearch/list.tpl.php');

$validTabs = array('chats','messages');

$tab = (isset($Params['user_parameters_unordered']['tab']) && in_array($Params['user_parameters_unordered']['tab'], $validTabs)) ? $Params['user_parameters_unordered']['tab'] : "chats";
$tpl->set('tab', $tab);

if (isset(erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionElasticsearch')->settings['columns'])) {
    $urlCfgDefault = ezcUrlConfiguration::getInstance();
    $url = erLhcoreClassURL::getInstance();
    foreach (erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionElasticsearch')->settings['columns'] as $columnField => $columnData) {
        $urlCfgDefault->addUnorderedParameter( $columnField );
        $url->applyConfiguration( $urlCfgDefault );
        $Params['user_parameters_unordered'][$columnField] = $url->getParam($columnField);
    }
}

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
        $chat_ids = explode(',',trim($filterParams['input_form']->chat_id));
        erLhcoreClassChat::validateFilterIn($chat_ids);
        $sparams['body']['query']['bool']['must'][]['terms']['chat_id'] = $chat_ids;
    }
    
    if ($filterParams['input_form']->nick != '') {
        $sparams['body']['query']['bool']['must'][]['match']['nick'] = $filterParams['input_form']->nick;
    }

    if ($filterParams['input_form']->proactive_chat != '') {
        $sparams['body']['query']['bool']['must'][]['term']['chat_initiator'] = (int)$filterParams['input_form']->proactive_chat;
    }

    if ($filterParams['input_form']->not_invitation != '') {
        $sparams['body']['query']['bool']['must'][]['term']['invitation_id'] = (int)$filterParams['input_form']->not_invitation;
    }

    if ($filterParams['input_form']->phone != '') {
        $sparams['body']['query']['bool']['must'][]['term']['phone'] = $filterParams['input_form']->phone;
    }
    
    if ($filterParams['input_form']->email != '') {
        $sparams['body']['query']['bool']['must'][]['term']['email'] = $filterParams['input_form']->email;
    }

    if ($filterParams['input_form']->region != '') {
        $sparams['body']['query']['bool']['must'][]['term']['region'] = $filterParams['input_form']->region;
    }
    
    if (trim((string)$filterParams['input_form']->user_id) != '') {
        $sparams['body']['query']['bool']['must'][]['term']['user_id'] = (int)trim($filterParams['input_form']->user_id);
    }
    
    if (trim((string)$filterParams['input_form']->department_id) != '') {
        $sparams['body']['query']['bool']['must'][]['term']['dep_id'] = (int)trim($filterParams['input_form']->department_id);
    }

    if (trim((string)$filterParams['input_form']->invitation_id) != '') {
        $sparams['body']['query']['bool']['must'][]['term']['invitation_id'] = (int)trim($filterParams['input_form']->invitation_id);
    }

    if (trim((string)$filterParams['input_form']->cls_us) != '') {
        $sparams['body']['query']['bool']['must'][]['term']['cls_us'] = (int)trim($filterParams['input_form']->cls_us);
    }

    if (trim((string)$filterParams['input_form']->has_unread_op_messages) != '') {
        $sparams['body']['query']['bool']['must'][]['term']['has_unread_op_messages'] = (int)trim($filterParams['input_form']->has_unread_op_messages);
    }

    if ($filterParams['input_form']->dropped_chat == true) {
        $sparams['body']['query']['bool']['must'][]['term']['drpd'] = 1;
    }

    if ($filterParams['input_form']->abandoned_chat == true) {
        $sparams['body']['query']['bool']['must'][]['term']['abnd'] = 1;
    }

    if (trim((string)$filterParams['input_form']->department_group_id) != '') {
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

    if (isset($filterParams['input']->country_ids) && is_array($filterParams['input']->country_ids) && !empty($filterParams['input']->country_ids)) {

        erLhcoreClassChat::validateFilterInString($filterParams['input']->country_ids);

        if (!empty($filterParams['input']->country_ids)) {
            $sparams['body']['query']['bool']['must'][]['terms']['country_code'] = $filterParams['input']->country_ids;
        }
    }

    if (isset($filterParams['input']->chat_status_ids) && is_array($filterParams['input']->chat_status_ids) && !empty($filterParams['input']->chat_status_ids)) {

        erLhcoreClassChat::validateFilterInString($filterParams['input']->chat_status_ids);

        if (!empty($filterParams['input']->chat_status_ids)) {
            $sparams['body']['query']['bool']['must'][]['terms']['status'] = $filterParams['input']->chat_status_ids;
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

    if ($filterParams['input_form']->has_operator == 1) {
        $sparams['body']['query']['bool']['must'][]['range']['user_id']['gt'] = (int)0;
    }

    if ($filterParams['input_form']->with_bot == 1) {
        $sparams['body']['query']['bool']['must'][]['range']['gbot_id']['gt'] = (int)0;
    }

    if ($filterParams['input_form']->transfer_happened == 1) {
        $sparams['body']['query']['bool']['must'][]['range']['transfer_uid']['gt'] = (int)0;
        $sparams['body']['query']['bool']['must'][]['range']['user_id']['gt'] = (int)0;
        $sparams['body']['query']['bool']['filter']['script']['script'] = "doc['user_id'].value != doc['transfer_uid'].value";
    }

    if ($filterParams['input_form']->without_bot == 1) {
        $sparams['body']['query']['bool']['must'][]['term']['gbot_id'] = 0;
    }

    if (isset(erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionElasticsearch')->settings['columns'])) {
        foreach (erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionElasticsearch')->settings['columns'] as $columnField => $columnData) {
            if ($columnData['filter_type'] == 'filterstring') {
                if (isset($filterParams['input_form']->{$columnField}) && trim($filterParams['input_form']->{$columnField}) != '') {
                    $sparams['body']['query']['bool']['must'][]['term'][$columnData['field_search']] = (string)$filterParams['input_form']->{$columnField};
                }
            } elseif ($columnData['filter_type'] == 'filterrangefloatgt') {
                if (isset($filterParams['input_form']->{$columnField}) && trim($filterParams['input_form']->{$columnField}) != '') {
                    $sparams['body']['query']['bool']['must'][]['range'][$columnData['field_search']]['gt'] = (float)$filterParams['input_form']->{$columnField};
                }
            } elseif ($columnData['filter_type'] == 'filterrangefloatlt') {
                if (isset($filterParams['input_form']->{$columnField}) && trim($filterParams['input_form']->{$columnField}) != '') {
                    $sparams['body']['query']['bool']['must'][]['range'][$columnData['field_search']]['lt'] = (float)$filterParams['input_form']->{$columnField};
                }
            }
        }
    }

    if (isset($filterParams['input']->bot_ids) && is_array($filterParams['input']->bot_ids) && !empty($filterParams['input']->bot_ids)) {

        erLhcoreClassChat::validateFilterInString($filterParams['input']->bot_ids);

        if (!empty($filterParams['input']->bot_ids)) {
            $sparams['body']['query']['bool']['must'][]['terms']['gbot_id'] = $filterParams['input']->bot_ids;
        }
    }

    if (isset($filterParams['input']->iwh_ids) && is_array($filterParams['input']->iwh_ids) && !empty($filterParams['input']->iwh_ids)) {

        erLhcoreClassChat::validateFilterInString($filterParams['input']->iwh_ids);

        if (!empty($filterParams['input']->iwh_ids)) {
            $sparams['body']['query']['bool']['must'][]['terms']['iwh_id'] = $filterParams['input']->iwh_ids;
        }
    }

    if (isset($filterParams['input']->subject_id) && is_array($filterParams['input']->subject_id) && !empty($filterParams['input']->subject_id)) {

        erLhcoreClassChat::validateFilterInString($filterParams['input']->subject_id);

        if (!empty($filterParams['input']->subject_id)) {
            $sparams['body']['query']['bool']['must'][]['terms']['subject_id'] = $filterParams['input']->subject_id;
        }
    }

    if (trim($filterParams['input_form']->uagent) != '') {
        $sparams['body']['query']['bool']['must'][]['match']['uagent'] = $filterParams['input_form']->uagent;
    }

    $filesFilter = array();
    if ($filterParams['input_form']->hvf == 1) {
        $filesFilter[]['term']['hvf'] = 1;
    }

    if ($filterParams['input_form']->hof == 1) {
        $filesFilter[]['term']['hof'] = 1;
    }

    if (!empty($filesFilter)) {
        $sparams['body']['query']['bool']['must'][]['bool']['should'] = $filesFilter;
    }

    // From what page customer start a chat
    if (trim($filterParams['input_form']->referrer) != '') {
        $sparams['body']['query']['bool']['should'][]['match']['referrer'] = $filterParams['input_form']->referrer;
        $sparams['body']['query']['bool']['minimum_should_match'] = 1; // Minimum one condition should be matched
    }

    // From what page customer come to our website
    if (trim($filterParams['input_form']->session_referrer) != '') {
        $sparams['body']['query']['bool']['should'][]['match']['session_referrer'] = $filterParams['input_form']->session_referrer;
        $sparams['body']['query']['bool']['minimum_should_match'] = 1; // Minimum one condition should be matched
    }

    if (trim($filterParams['input_form']->keyword) != '') {

        $exactMatch = $filterParams['input_form']->exact_match == 1 ? 'match_phrase' : 'match';

        $paramQuery = [
            'query' => $filterParams['input_form']->keyword
        ];

        if ($filterParams['input_form']->fuzzy == 1 && $filterParams['input_form']->exact_match != 1) {
            $paramQuery['fuzziness'] = 'AUTO';
            $paramQuery['prefix_length'] = max((mb_strlen($filterParams['input_form']->keyword) - (is_numeric($filterParams['input_form']->fuzzy_prefix) ? $filterParams['input_form']->fuzzy_prefix : 1)),0);
        }

        if (empty($filterParams['input_form']->search_in) || in_array(1,$filterParams['input_form']->search_in)) {
            $sparams['body']['query']['bool']['should'][][$exactMatch]['msg_visitor'] = $paramQuery;
            $sparams['body']['query']['bool']['should'][][$exactMatch]['msg_operator'] = $paramQuery;
            $sparams['body']['query']['bool']['should'][][$exactMatch]['msg_system'] = $paramQuery;
        } else {
            if (in_array(2,$filterParams['input_form']->search_in)) {
                $sparams['body']['query']['bool']['should'][][$exactMatch]['msg_visitor'] = $paramQuery;
            }
            
            if (in_array(3,$filterParams['input_form']->search_in)) {
                $sparams['body']['query']['bool']['should'][][$exactMatch]['msg_operator'] = $paramQuery;
            }
            
            if (in_array(4,$filterParams['input_form']->search_in)) {
                $sparams['body']['query']['bool']['should'][][$exactMatch]['msg_system'] = $paramQuery;
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

        if (trim($filterParams['input_form']->chat_id) != '') {
            foreach ($chat_ids as $index_chat_id => $chat_id) {
                $chatDirect = erLhcoreClassModelChat::fetch((int)trim($chat_id));

                if (!($chatDirect instanceof erLhcoreClassModelChat)) {
                    $chatArchive = erLhcoreClassChatArcive::fetchChatById((int)trim($chat_id));
                    if (is_array($chatArchive)) {
                        $chatDirect = $chatArchive['chat'];
                    }
                }

                if (is_object($chatDirect)) {
                    if ($index_chat_id == 0) {
                        $sparams = array(
                            'body' => array()
                        );
                        $sparams['body']['query']['bool']['must'][]['terms']['chat_id'] = $chat_ids;
                    }

                    if (isset($dateFilter['gte'])) {
                        $dateFilter['gte'] = min($chatDirect->time + 10, $dateFilter['gte']);
                    } else {
                        $dateFilter['gte'] = $chatDirect->time + 10;
                    }

                    if (isset($dateFilter['lte'])) {
                        $dateFilter['lte'] = max($chatDirect->time - 10,$dateFilter['lte']);
                    } else {
                        $dateFilter['lte'] = $chatDirect->time - 10;
                    }
                }
            }
        }

        if (isset($Params['user_parameters_unordered']['export']) && $Params['user_parameters_unordered']['export'] == 1) {
            if (ezcInputForm::hasPostData()) {
                session_write_close();
                $ignoreFields = (new erLhcoreClassModelChat)->getState();
                unset($ignoreFields['id']);
                $ignoreFields = array_keys($ignoreFields);

                $filterSQL = [];

                $chats = erLhcoreClassModelESChat::getList(array(
                    'offset' => 0,
                    'limit' => 9000,
                    'body' => array_merge(array(
                        'sort' => $sort
                    ), $sparams['body'])
                ),
                    array('date_index' => $dateFilter));

                $chatIDs = [];
                foreach ($chats as $chatID) {
                    $filterSQL['filterin']['id'][] = $chatID->chat_id;
                }

                // @todo add archived chats support as not all elastic chats are in live tables

                erLhcoreClassChatExport::chatListExportXLS(erLhcoreClassModelChat::getList(array_merge($filterSQL, array('limit' => 100000, 'offset' => 0, 'ignore_fields' => $ignoreFields))), array('csv' => isset($_POST['CSV']), 'type' => (isset($_POST['exportOptions']) ? $_POST['exportOptions'] : [])));
                exit;
            } else {
                $tpl = erLhcoreClassTemplate::getInstance('lhchat/export_config.tpl.php');
                $tpl->set('action_url', erLhcoreClassDesign::baseurl('elasticsearch/list') . erLhcoreClassSearchHandler::getURLAppendFromInput($filterParams['input_form']));
                echo $tpl->fetch();
                exit;
            }
        }


        if (isset($Params['user_parameters_unordered']['export']) && $Params['user_parameters_unordered']['export'] == 2) {

            $savedSearch = new erLhAbstractModelSavedSearch();

            if ($Params['user_parameters_unordered']['view'] > 0) {
                $savedSearchPresent = erLhAbstractModelSavedSearch::fetch($Params['user_parameters_unordered']['view']);
                if ($savedSearchPresent->user_id == $currentUser->getUserID()) {
                    $savedSearch = $savedSearchPresent;
                }
            }

            $tpl = erLhcoreClassTemplate::getInstance('lhviews/save_chat_view.tpl.php');
            $tpl->set('action_url', erLhcoreClassDesign::baseurl('elasticsearch/list') . erLhcoreClassSearchHandler::getURLAppendFromInput($filterParams['input_form']));
            if (ezcInputForm::hasPostData()) {
                $Errors = erLhcoreClassAdminChatValidatorHelper::validateSavedSearch($savedSearch, array(
                    'sort' => $sort,
                    'sparams' => $sparams,
                    'filter' => $filterParams['filter'],
                    'input_form' => $filterParams['input_form']
                ));
                if (empty($Errors)) {
                    $savedSearch->user_id = $currentUser->getUserID();
                    $savedSearch->scope = 'eschat';
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

            $iconsAdditional = erLhAbstractModelChatColumn::getList(array('ignore_fields' => array('position','conditions','column_identifier','enabled'), 'sort' => false, 'filter' => array('icon_mode' => 1, 'enabled' => 1, 'chat_enabled' => 1)));
            $iconsAdditionalColumn = erLhAbstractModelChatColumn::getList(array('ignore_fields' => array('position','conditions','column_identifier','enabled'), 'sort' => 'position ASC, id ASC','filter' => array('enabled' => 1, 'icon_mode' => 0, 'chat_list_enabled' => 1)));

            erLhcoreClassChat::prefillGetAttributes($chats, array(), array(), array('additional_columns' => ($iconsAdditional + $iconsAdditionalColumn), 'do_not_clean' => true));

            $tpl->set('icons_additional',$iconsAdditional);
            $tpl->set('additional_chat_columns',$iconsAdditionalColumn);
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
$tpl->set('Result',['path' => array(
    array(
        'url' => erLhcoreClassDesign::baseurl('elasticsearch/index'),
        'title' => erTranslationClassLhTranslation::getInstance()->getTranslation('lhelasticsearch/module', 'Elastic Search')
    ),
    array(
        'url' => erLhcoreClassDesign::baseurl('elasticsearch/list'),
        'title' => erTranslationClassLhTranslation::getInstance()->getTranslation('lhelasticsearch/list', 'Chat list')
    )
)]);
$Result['body_class'] = 'h-100 dashboard-height';
$Result['content'] = $tpl->fetch();

/*$Result['path'] = ;*/
