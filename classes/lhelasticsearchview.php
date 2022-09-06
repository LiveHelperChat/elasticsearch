<?php

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