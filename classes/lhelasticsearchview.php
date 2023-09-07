<?php
#[\AllowDynamicProperties]
class erLhcoreClassElasticSearchView
{
    // View edit handler
    public static function editView($params) {
        if ($params['search']->scope == 'eschat') {
            $append = erLhcoreClassSearchHandler::getURLAppendFromInput($params['search']->params_array['input_form']);
            erLhcoreClassModule::redirect('elasticsearch/list', '/(view)/' . $params['search']->id . $append);
            exit;
        } elseif ($params['search']->scope == 'esmail') {
            $append = erLhcoreClassSearchHandler::getURLAppendFromInput($params['search']->params_array['input_form']);
            erLhcoreClassModule::redirect('elasticsearch/listmail', '/(view)/' . $params['search']->id . $append);
            exit;
        }
    }

    // Update view handler
    public static function exportView($params) {
        if ($params['search']->scope == 'eschat')
        {
            $tpl = erLhcoreClassTemplate::getInstance('lhchat/export_config.tpl.php');
            $tpl->set('action_url', erLhcoreClassDesign::baseurl('elasticsearch/list') . $params['append']);
            echo $tpl->fetch();
            exit;
        } else if ($params['search']->scope == 'esmail') {
            $tpl = erLhcoreClassTemplate::getInstance('lhmailconv/export_config.tpl.php');
            $tpl->set('action_url', erLhcoreClassDesign::baseurl('elasticsearch/listmail') . $params['append']);
            echo $tpl->fetch();
            exit;
        }
    }

    public static function updateView($params) {
        if ($params['search']->scope == 'eschat')
        {
            $dateFilter = [];

            if ($params['search']->days > 0) {
                $dateFilter['gte'] = time() - $params['search']->days * 24 * 3600;
            }

            if (isset($params['search']->params_array['sparams']['body']['query']['bool']['must'])) {
                $mustPresent = $params['search']->params_array['sparams']['body']['query']['bool']['must'];
                foreach ($mustPresent as $indexMust => $mustItem) {
                    if (isset($mustItem['terms']['dep_id']) || isset($mustItem['terms']['user_id'])) {
                        unset($mustPresent[$indexMust]);
                    }
                }
                self::applyDynamicFilter($mustPresent, (object)$params['search']->params_array['input_form'], ['user_attr' => 'user_id']);
            }

            if (isset($mustPresent) && !empty($mustPresent)) {
                $params['search']->params_array['sparams']['body']['query']['bool']['must'] = array_values($mustPresent);
            }

            $totalRecords = erLhcoreClassModelESChat::getCount($params['search']->params_array['sparams'], array('date_index' => $dateFilter));

            $params['search']->updated_at = time();

            if ($params['search']->total_records != $totalRecords) {
                $params['search']->total_records = $totalRecords;
                $params['search']->updateThis(['update' => ['updated_at','total_records']]);
            } else {
                $params['search']->updateThis(['update' => ['updated_at']]);
            }

        } else if ($params['search']->scope == 'esmail') {
            $dateFilter = [];

            if ($params['search']->days > 0) {
                $dateFilter['gte'] = time() - $params['search']->days * 24 * 3600;
            }

            if (isset($params['search']->params_array['sparams']['body']['query']['bool']['must'])) {
                $mustPresent = $params['search']->params_array['sparams']['body']['query']['bool']['must'];
                foreach ($mustPresent as $indexMust => $mustItem) {
                    if (isset($mustItem['terms']['dep_id']) || isset($mustItem['terms']['conv_user_id'])) {
                        unset($mustPresent[$indexMust]);
                    }
                }

                self::applyDynamicFilter($mustPresent, (object)$params['search']->params_array['input_form'], ['user_attr' => 'conv_user_id']);
            }

            if (isset($mustPresent) && !empty($mustPresent)) {
                $params['search']->params_array['sparams']['body']['query']['bool']['must'] = array_values($mustPresent);
            }

            $totalRecords = erLhcoreClassModelESMail::getCount($params['search']->params_array['sparams'], array('date_index' => $dateFilter));

            $params['search']->updated_at = time();

            if ($params['search']->total_records != $totalRecords) {
                $params['search']->total_records = $totalRecords;
                $params['search']->updateThis(['update' => ['updated_at','total_records']]);
            } else {
                $params['search']->updateThis(['update' => ['updated_at']]);
            }
        }

    }

    public static function applyDynamicFilter( & $termsFilter, $input, $attrOptions)
    {
        // User ids, group, group ids
        if (isset($input->group_ids) && is_array($input->group_ids) && !empty($input->group_ids)) {

            erLhcoreClassChat::validateFilterIn($input->group_ids);

            $db = ezcDbInstance::get();
            $stmt = $db->prepare('SELECT user_id FROM lh_groupuser WHERE group_id IN (' . implode(',',$input->group_ids) .')');
            $stmt->execute();
            $userIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($userIds)) {
                $termsFilter[]['terms'][$attrOptions['user_attr']] = $userIds;
            }
        }

        if (isset($input->user_ids) && is_array($input->user_ids) && !empty($input->user_ids)) {
            erLhcoreClassChat::validateFilterIn($input->user_ids);
            $termsFilter[]['terms'][$attrOptions['user_attr']] = $input->user_ids;
        }

        if (isset($input->group_id) && is_numeric($input->group_id) && $input->group_id > 0 ) {
            $db = ezcDbInstance::get();
            $stmt = $db->prepare('SELECT user_id FROM lh_groupuser WHERE group_id = :group_id');
            $stmt->bindValue( ':group_id', $input->group_id, PDO::PARAM_INT);
            $stmt->execute();
            $userIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($userIds)) {
                $termsFilter[]['terms'][$attrOptions['user_attr']] = $userIds;
            }
        }

        if (trim((string)$input->department_group_id) != '') {
            $db = ezcDbInstance::get();
            $stmt = $db->prepare('SELECT dep_id FROM lh_departament_group_member WHERE dep_group_id = :group_id');
            $stmt->bindValue( ':group_id', $input->department_group_id, PDO::PARAM_INT);
            $stmt->execute();
            $depIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($depIds)) {
                $termsFilter[]['terms']['dep_id'] = $depIds;
            }
        }

        if (isset($input->department_group_ids) && is_array($input->department_group_ids) && !empty($input->department_group_ids)) {

            erLhcoreClassChat::validateFilterIn($input->department_group_ids);

            $db = ezcDbInstance::get();
            $stmt = $db->prepare('SELECT dep_id FROM lh_departament_group_member WHERE dep_group_id IN (' . implode(',',$input->department_group_ids) . ')');
            $stmt->execute();
            $depIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($depIds)) {
                $termsFilter[]['terms']['dep_id'] = $depIds;
            }
        }

        if (isset($input->department_ids) && is_array($input->department_ids) && !empty($input->department_ids)) {
            erLhcoreClassChat::validateFilterIn($input->department_ids);
            $termsFilter[]['terms']['dep_id'] = $input->department_ids;
        }
    }

    // Load view handler
    public static function loadView($params)
    {
        $search = $params['search'];

        if ($search->scope == 'eschat') {
            $tpl = erLhcoreClassTemplate::getInstance('lhviews/eschat.tpl.php');
            $tpl->set('search', $search);

            $dateFilter = [];

            if ($search->days > 0) {
                $dateFilter['gte'] = time() - $search->days * 24 * 3600;
            }

            if (isset($search->params_array['sparams']['body']['query']['bool']['must'])) {
                $mustPresent = $search->params_array['sparams']['body']['query']['bool']['must'];
                foreach ($mustPresent as $indexMust => $mustItem) {
                    if (isset($mustItem['terms']['dep_id']) || isset($mustItem['terms']['user_id'])) {
                        unset($mustPresent[$indexMust]);
                    }
                }
                self::applyDynamicFilter($mustPresent, (object)$search->params_array['input_form'], ['user_attr' => 'user_id']);
            }

            if (isset($mustPresent) && !empty($mustPresent)) {
                $search->params_array['sparams']['body']['query']['bool']['must'] = array_values($mustPresent);
            }

            $total = erLhcoreClassModelESChat::getCount($search->params_array['sparams'], array('date_index' => $dateFilter));

            $pages = new lhPaginator();
            $startTime = microtime();
            $params['total_records'] = $pages->items_total = $total;
            erLhcoreClassViewResque::logSlowView($startTime, microtime(), $search);

            $pages->translationContext = 'chat/pendingchats';
            $pages->serverURL = erLhcoreClassDesign::baseurl('views/loadview') . '/' . $search->id;
            $pages->paginate();
            $tpl->set('pages', $pages);

            $sparams = $search->params_array['sparams']['body'];

            if (isset($sparams['highlight']['fields']['msg_operator'])) {
                $sparams['highlight']['fields']['msg_operator'] = new stdClass();
                $sparams['highlight']['fields']['msg_visitor'] = new stdClass();
                $sparams['highlight']['fields']['msg_system'] = new stdClass();
            }

            $items = erLhcoreClassModelESChat::getList(array(
                'offset' => $pages->low,
                'limit' => $pages->items_per_page,
                'body' => array_merge(array(
                    'sort' => $search->params_array['sort']
                ), $sparams)
            ),
                array('date_index' => $dateFilter));

            $chatIds = array();
            foreach ($items as $prevChat) {
                $chatIds[$prevChat->chat_id] = array();
            }
            erLhcoreClassChatArcive::setArchiveAttribute($chatIds);

            $iconsAdditional = erLhAbstractModelChatColumn::getList(array('ignore_fields' => array('position','conditions','column_identifier','enabled'), 'sort' => false, 'filter' => array('icon_mode' => 1, 'enabled' => 1, 'chat_enabled' => 1)));
            erLhcoreClassChat::prefillGetAttributes($items, array(), array(), array('additional_columns' => $iconsAdditional, 'do_not_clean' => true));

            $tpl->set('icons_additional',$iconsAdditional);
            $tpl->set('itemsArchive', $chatIds);
            $tpl->set('items', $items);
            $tpl->set('list_mode', $params['uparams']['mode'] == 'list');

            // Update view data, so background worker do nothing
            $search->total_records = (int)$params['total_records'];
            $search->updated_at = time();
            $search->requested_at = time();
            $search->updateThis(['update' => ['total_records', 'updated_at', 'requested_at']]);
            $params['content'] = $tpl->fetch();

            return array('status' => erLhcoreClassChatEventDispatcher::STOP_WORKFLOW);
        }

        if ($search->scope == 'esmail') {
            $tpl = erLhcoreClassTemplate::getInstance('lhviews/esmail.tpl.php');
            $tpl->set('search', $search);

            $dateFilter = [];

            if ($search->days > 0) {
                $dateFilter['gte'] = time() - $search->days * 24 * 3600;
            }

            if (isset($search->params_array['sparams']['body']['query']['bool']['must'])) {
                $mustPresent = $search->params_array['sparams']['body']['query']['bool']['must'];
                foreach ($mustPresent as $indexMust => $mustItem) {
                    if (isset($mustItem['terms']['dep_id']) || isset($mustItem['terms']['conv_user_id'])) {
                        unset($mustPresent[$indexMust]);
                    }
                }

                self::applyDynamicFilter($mustPresent, (object)$search->params_array['input_form'], ['user_attr' => 'conv_user_id']);
            }

            if (isset($mustPresent) && !empty($mustPresent)) {
                $search->params_array['sparams']['body']['query']['bool']['must'] = array_values($mustPresent);
            }

            $total = erLhcoreClassModelESMail::getCount($search->params_array['sparams'], array('date_index' => $dateFilter));

            $pages = new lhPaginator();
            $startTime = microtime();
            $params['total_records'] = $pages->items_total = $total;
            erLhcoreClassViewResque::logSlowView($startTime, microtime(), $search);

            $pages->translationContext = 'chat/pendingchats';
            $pages->serverURL = erLhcoreClassDesign::baseurl('views/loadview') . '/' . $search->id;
            $pages->paginate();
            $tpl->set('pages', $pages);

            $sparams = $search->params_array['sparams']['body'];

            if (isset($sparams['highlight']['fields']['subject'])) {
                $sparams['highlight']['fields']['subject'] = new stdClass();
                $sparams['highlight']['fields']['alt_body'] = new stdClass();
            }

            $items = erLhcoreClassModelESMail::getList(array(
                'offset' => $pages->low,
                'limit' => $pages->items_per_page,
                'body' => array_merge(array(
                    'sort' => $search->params_array['sort']
                ), $sparams)
            ),
                array('date_index' => $dateFilter));

            $previousConversation = null;
            foreach ($items as $prevChat) {
                if (is_object($previousConversation) && $previousConversation->conversation_id == $prevChat->conversation_id) {
                    $previousConversation->has_many_messages = true;
                }
                $previousConversation = $prevChat;
            }

            $tpl->set('items', $items);
            $tpl->set('list_mode', $params['uparams']['mode'] == 'list');

            // Update view data, so background worker do nothing
            $search->total_records = (int)$params['total_records'];
            $search->updated_at = time();
            $search->requested_at = time();
            $search->updateThis(['update' => ['total_records', 'updated_at', 'requested_at']]);
            $params['content'] = $tpl->fetch();

            return array('status' => erLhcoreClassChatEventDispatcher::STOP_WORKFLOW);
        }
    }

}