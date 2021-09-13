<?php

class erLhcoreClassElasticSearchView
{
    // View edit handler
    public static function editView($params) {
        if ($params['search']->scope == 'eschat') {
            $append = erLhcoreClassSearchHandler::getURLAppendFromInput($params['search']->params_array['input_form']);
            erLhcoreClassModule::redirect('elasticsearch/list', '/(view)/' . $params['search']->id . $append);
            exit;
        }
    }

    // Update view handler
    public static function updateView($params) {

        if ($params['search']->scope != 'eschat') {
            return;
        }

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
    }

    // Load view handler
    public static function loadView($params)
    {
        $search = $params['search'];

        if ($search->scope != 'eschat') {
            return;
        }

        $tpl = erLhcoreClassTemplate::getInstance( 'lhviews/eschat.tpl.php');
        $tpl->set('search',$search);

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
        $pages->serverURL = erLhcoreClassDesign::baseurl('views/loadview').'/'.$search->id;
        $pages->paginate();
        $tpl->set('pages',$pages);

        $items = erLhcoreClassModelESChat::getList(array(
            'offset' => $pages->low,
            'limit' => $pages->items_per_page,
            'body' => array_merge(array(
                'sort' => $search->params_array['sort']
            ), $search->params_array['sparams']['body'])
        ),
        array('date_index' => $dateFilter));

        $chatIds = array();
        foreach ($items as $prevChat) {
            $chatIds[$prevChat->chat_id] = array();
        }
        erLhcoreClassChatArcive::setArchiveAttribute($chatIds);
        $tpl->set('itemsArchive', $chatIds);
        $tpl->set('items', $items);
        $tpl->set('list_mode', $params['uparams']['mode'] == 'list');

        // Update view data, so background worker do nothing
        $search->total_records = (int)$params['total_records'];
        $search->updated_at = time();
        $search->requested_at = time();
        $search->updateThis(['update' => ['total_records','updated_at','requested_at']]);
        $params['content'] = $tpl->fetch();

        return array('status' => erLhcoreClassChatEventDispatcher::STOP_WORKFLOW);
    }

}