<?php
#[\AllowDynamicProperties]
class erLhcoreClassElasticSearchStatistic
{
    public static function uparamsAppend($params) {
        $urlCfgDefault = ezcUrlConfiguration::getInstance();
        $url = erLhcoreClassURL::getInstance();
        foreach (erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionElasticsearch')->settings['columns'] as $column => $columnFilter) {
            $urlCfgDefault->addUnorderedParameter($column);
            $url->applyConfiguration( $urlCfgDefault );
            $params['uparams'][$column] = $url->getParam($column);
        }
    }

    public static function getIndexByFilter($filter, $elasticType) {

        $dateIndexFilter = array();

        if (isset($filter['filtergte']['time']) && $filter['filtergte']['time'] > 0) {
            $dateIndexFilter['gte'] = $filter['filtergte']['time'];
        }

        if (isset($filter['filterlte']['time']) && $filter['filterlte']['time'] > 0) {
            $dateIndexFilter['lte'] = $filter['filterlte']['time'];
        }

        $indexSearch = erLhcoreClassModelESChat::extractIndexFilter($dateIndexFilter, erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionElasticsearch')->settings['index'], $elasticType);

        return $indexSearch;
    }

    public static function statisticGetratingbyuser($params){

        $elasticSearchHandler = erLhcoreClassElasticClient::getHandler();

        $sparams = array();
        $sparams['index'] = erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionElasticsearch')->settings['index_search'] . '-' . erLhcoreClassModelESChat::$elasticType;
        $sparams['ignore_unavailable'] = true;

        if (isset($_GET['abandoned_chat']) && $_GET['abandoned_chat'] == 1) {
            $params['filter']['filter']['abnd'] = 1;
        }

        if (isset($_GET['dropped_chat']) && $_GET['dropped_chat'] == 1) {
            $params['filter']['filter']['drpd'] = 1;
        }

        if (isset($_GET['transfer_happened']) && $_GET['transfer_happened'] == 1) {
            $sparams['body']['query']['bool']['must'][]['range']['transfer_uid']['gt'] = (int)0;
            $sparams['body']['query']['bool']['must'][]['range']['user_id']['gt'] = (int)0;
            $sparams['body']['query']['bool']['filter']['script']['script'] = "doc['user_id'].value != doc['transfer_uid'].value";
        }

        self::formatFilter($params['filter'], $sparams);

        if (! isset($params['filter']['filtergte']['time']) && ! isset($params['filter']['filterlte']['time'])) {
            $ts = mktime(0, 0, 0, date('m'), date('d') - $params['days'], date('y'));
            $sparams['body']['query']['bool']['must'][]['range']['time']['gt'] = $ts * 1000;
            $params['filter']['filtergte']['time'] = $ts;
        }

        $indexSearch = self::getIndexByFilter($params['filter'], erLhcoreClassModelESChat::$elasticType);

        if ($indexSearch != '') {
            $sparams['index'] = $indexSearch;
        }

        $sparams['body']['size'] = 0;
        $sparams['body']['from'] = 0;

        $sparams['body']['aggs']['group_by_user']['terms']['field'] = 'user_id';
        $sparams['body']['aggs']['group_by_user']['terms']['size'] = 40;

        $paramsRating = $sparams;
        $paramsRating['body']['query']['bool']['must'][]['term']['fbst'] = 1;
        $response['thumbsup'] = $elasticSearchHandler->search($paramsRating)['aggregations']['group_by_user']['buckets'];

        $paramsRating = $sparams;
        $paramsRating['body']['query']['bool']['must'][]['term']['fbst'] = 2;
        $response['thumbdown'] = $elasticSearchHandler->search($paramsRating)['aggregations']['group_by_user']['buckets'];

        $paramsRating = $sparams;
        $paramsRating['body']['query']['bool']['must'][]['term']['fbst'] = 0;
        $response['unrated'] = $elasticSearchHandler->search($paramsRating)['aggregations']['group_by_user']['buckets'];

        $responseFormatted = [];

        foreach ($response as $bucket => $docs) {
            foreach ($docs as $doc) {
                $responseFormatted[$bucket][] = ['number_of_chats' => $doc['doc_count'], 'user_id' => $doc['key']];
            }
        }

        return array(
            'status' => erLhcoreClassChatEventDispatcher::STOP_WORKFLOW,
            'list' => $responseFormatted
        );
    }

    public static function statisticGettopchatsbycountry($params)
    {
        $elasticSearchHandler = erLhcoreClassElasticClient::getHandler();
        
        $sparams = array();
        $sparams['index'] = erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionElasticsearch')->settings['index_search'] . '-' . erLhcoreClassModelESChat::$elasticType;
        $sparams['ignore_unavailable'] = true;

        self::formatFilter($params['filter'], $sparams);
        
        if (! isset($params['filter']['filtergte']['time']) && ! isset($params['filter']['filterlte']['time'])) {
            $ts = mktime(0, 0, 0, date('m'), date('d') - $params['days'], date('y'));
            $sparams['body']['query']['bool']['must'][]['range']['time']['gt'] = $ts * 1000;
            $params['filter']['filtergte']['time'] = $ts;
        }
        
        if (isset($_GET['abandoned_chat']) && $_GET['abandoned_chat'] == 1) {
            $params['filter']['filter']['abnd'] = 1;
        }

        if (isset($_GET['dropped_chat']) && $_GET['dropped_chat'] == 1) {
            $params['filter']['filter']['drpd'] = 1;
        }

        if (isset($_GET['transfer_happened']) && $_GET['transfer_happened'] == 1) {
            $sparams['body']['query']['bool']['must'][]['range']['transfer_uid']['gt'] = (int)0;
            $sparams['body']['query']['bool']['must'][]['range']['user_id']['gt'] = (int)0;
            $sparams['body']['query']['bool']['filter']['script']['script'] = "doc['user_id'].value != doc['transfer_uid'].value";
        }

        $indexSearch = self::getIndexByFilter($params['filter'], erLhcoreClassModelESChat::$elasticType);

        if ($indexSearch != '') {
            $sparams['index'] = $indexSearch;
        }

        $sparams['body']['size'] = 0;
        $sparams['body']['from'] = 0;
        $sparams['body']['aggs']['group_by_country_count']['terms']['field'] = 'country_name';
        $sparams['body']['aggs']['group_by_country_count']['terms']['size'] = 40;

        $response = $elasticSearchHandler->search($sparams);
        
        $statsAggr = array();
        
        foreach ($response['aggregations']['group_by_country_count']['buckets'] as $item) {
            $statsAggr[] = array(
                'number_of_chats' => $item['doc_count'],
                'country_name' => (trim($item['key']) == '' ? '-' : $item['key'])
            );
        }
        
        return array(
            'status' => erLhcoreClassChatEventDispatcher::STOP_WORKFLOW,
            'list' => $statsAggr
        );
    }
    
    /**
     * Appends statistic tab as valid option
     *
     * @param array $params
     */
    public static function appendStatisticTab($params) {
        $params['valid_tabs'][] = 'pendingvsonlineop';
    }
    
    public static function getGroupBy()
    {
        $items = array();

        $item = new stdClass();
        $item->id = 60*1000;
        $item->name = "Minute";
        $items[$item->id] = $item;

        $item = new stdClass();
        $item->id = 2*60*1000;
        $item->name = "2 Minutes";
        $items[$item->id] = $item;

        $item = new stdClass();
        $item->id = 3*60*1000;
        $item->name = "3 Minutes";
        $items[$item->id] = $item;

        $item = new stdClass();
        $item->id = 4*60*1000;
        $item->name = "4 Minutes";
        $items[$item->id] = $item;

        $item = new stdClass();
        $item->id = 5*60*1000;
        $item->name = "5 Minutes";
        $items[$item->id] = $item;

        $item = new stdClass();
        $item->id = 10*60*1000;
        $item->name = "10 Minutes";
        $items[$item->id] = $item;

        $item = new stdClass();
        $item->id = 30*60*1000;
        $item->name = "30 Minutes";
        $items[$item->id] = $item;

        $item = new stdClass();
        $item->id = 60*60*1000;
        $item->name = "1 Hour";
        $items[$item->id] = $item;

        $item = new stdClass();
        $item->id = 4*60*60*1000;
        $item->name = "4 Hours";
        $items[$item->id] = $item;

        return $items;
    }
    
    /**
     * Process this option
     *
     * @param array $paramsExecution
     */
    public static function processTab($paramsExecution) {
    
        $Params = $paramsExecution['params'];
    
        if ($Params['user_parameters_unordered']['tab'] == 'pendingvsonlineop')
        {
            $elasticSearchHandler = erLhcoreClassElasticClient::getHandler();

            if (isset($_GET['doSearch'])) {
                $filterParams = erLhcoreClassSearchHandler::getParams(array('customfilterfile' => 'extension/elasticsearch/classes/searchattr/pendingvsonlineop.php', 'format_filter' => true, 'use_override' => true, 'uparams' => $Params['user_parameters_unordered']));
            } else {
                $filterParams = erLhcoreClassSearchHandler::getParams(array('customfilterfile' => 'extension/elasticsearch/classes/searchattr/pendingvsonlineop.php', 'format_filter' => true, 'uparams' => $Params['user_parameters_unordered']));
            }

            if (!isset($_GET['doSearch']) && !isset($Params['user_parameters_unordered']['xls'])) {
                $tpl = $paramsExecution['tpl'];
                $tpl->set('input',$filterParams['input_form']);
                $tpl->set('statistic', []);
                $tpl->set('do_search_first', true);
                return;
            }

            $groupByData = array(
                'interval' => 300000,
                'divide' => 1
            );
            
            $groupOptions = self::getGroupBy();
            if (!empty($filterParams['input_form']->group_by) && isset($groupOptions[$filterParams['input_form']->group_by])) {
                $groupByData = array(
                    'interval' => (int)$filterParams['input_form']->group_by,
                    'divide' => round($filterParams['input_form']->group_by/(60000))
                );
            }
            
            $sparams = array();
            $sparams['index'] = erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionElasticsearch')->settings['index_search'] . '-' . erLhcoreClassModelESPendingChat::$elasticType;
            $sparams['ignore_unavailable'] = true;

            $filterParamsIndex = array();

            if (! isset($filterParams['filter']['filtergte']['itime']) && ! isset($filterParams['filter']['filterlte']['itime'])) {
                $filterParams['filter']['filtergte']['itime'] = time()-(24*3600);
            }

            if (isset($filterParams['filter']['filtergte']['itime'])) {
                $filterParamsIndex['filter']['filtergte']['time'] = $filterParams['filter']['filtergte']['itime'];
            }

            if (isset($filterParams['filter']['filterlte']['itime'])) {
                $filterParamsIndex['filter']['filterlte']['time'] = $filterParams['filter']['filterlte']['itime'];
            }

            $indexSearch = self::getIndexByFilter($filterParamsIndex['filter'], erLhcoreClassModelESPendingChat::$elasticType);

            if ($indexSearch != '') {
                $sparams['index'] = $indexSearch;
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

            self::formatFilter($filterChats['filter'], $sparams);

            $sparams['body']['size'] = 0;
            $sparams['body']['from'] = 0;
            $sparams['body']['aggs']['chats_over_time']['date_histogram']['field'] = 'itime';
            $sparams['body']['aggs']['chats_over_time']['date_histogram']['interval'] = $groupByData['interval'];

            $sparams['body']['aggs']['chats_over_time']['date_histogram']['time_zone'] = self::getTimeZone();
            
            $sparams['body']['aggs']['chats_over_time']['aggs']['chat_status']['terms']['field'] = 'status';
            $response = $elasticSearchHandler->search($sparams);


            $numberOfChats = array();
           
            $keyStatus = array(
                erLhcoreClassModelChat::STATUS_ACTIVE_CHAT => 'active',
                erLhcoreClassModelChat::STATUS_PENDING_CHAT => 'pending'
            );

            if (isset($response['aggregations']['chats_over_time']['buckets']))
            {
                foreach ($response['aggregations']['chats_over_time']['buckets'] as $bucket) {

                    $indexBucket = $bucket['key']/1000;

                    foreach ($bucket['chat_status']['buckets'] as $bucket) {
                        if (isset($keyStatus[$bucket['key']])) {
                            $numberOfChats[$indexBucket][$keyStatus[$bucket['key']]] = $bucket['doc_count'] / $groupByData['divide'];
                        }
                    }

                    foreach ($keyStatus as $mustHave) {
                        if (! isset($numberOfChats[$indexBucket][$mustHave])) {
                            $numberOfChats[$indexBucket][$mustHave] = 0;
                        }
                    }

                    $numberOfChats[$indexBucket]['op_count'] = 0;
                }
            }

            $sparams = array();
            $sparams['index'] = erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionElasticsearch')->settings['index_search'] . '-' . erLhcoreClassModelESOnlineOperator::$elasticType;
            $sparams['ignore_unavailable'] = true;

            $indexSearch = self::getIndexByFilter($filterParamsIndex['filter'], erLhcoreClassModelESOnlineOperator::$elasticType);

            if ($indexSearch != '') {
                $sparams['index'] = $indexSearch;
            }

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

            self::formatFilter($filterParams['filter'], $sparams);

            $sparams['body']['size'] = 0;
            $sparams['body']['from'] = 0;
            $sparams['body']['aggs']['chats_over_time']['date_histogram']['field'] = 'itime';
            $sparams['body']['aggs']['chats_over_time']['date_histogram']['interval'] = $groupByData['interval'];
            $sparams['body']['aggs']['chats_over_time']['date_histogram']['time_zone'] = self::getTimeZone();

            $response = $elasticSearchHandler->search($sparams);

            if (isset($response['aggregations']['chats_over_time']['buckets']))
            {
                foreach ($response['aggregations']['chats_over_time']['buckets'] as $bucket) {
                    $indexBucket = $bucket['key']/1000;
                    $numberOfChats[$indexBucket]['op_count'] = $bucket['doc_count'] / $groupByData['divide'];

                    foreach ($keyStatus as $mustHave) {
                        if (! isset($numberOfChats[$indexBucket][$mustHave])) {
                            $numberOfChats[$indexBucket][$mustHave] = 0;
                        }
                    }
                }
            }

            if (isset($Params['user_parameters_unordered']['xls']) && $Params['user_parameters_unordered']['xls'] == 1) {
                self::exportXLSPendingOnlineOperators($numberOfChats);
            }

            // We have to sort because some indexes get's appended at the bottom
            ksort($numberOfChats);

            $tpl = $paramsExecution['tpl'];
            $tpl->set('input',$filterParams['input_form']);
            $tpl->set('statistic', $numberOfChats);
        }
    }

    public static function exportXLSPendingOnlineOperators($data)
    {
        include 'lib/core/lhform/PHPExcel.php';
        $cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
        $cacheSettings = array( 'memoryCacheSize ' => '64MB');
        PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);

        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->getStyle('A1:AW1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->setTitle('Report');

        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, 1, erTranslationClassLhTranslation::getInstance()->getTranslation('chat/chatexport','Date'));
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, 1, erTranslationClassLhTranslation::getInstance()->getTranslation('chat/chatexport','Online Operators'));
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, 1, erTranslationClassLhTranslation::getInstance()->getTranslation('chat/chatexport','Pending Chats'));
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, 1, erTranslationClassLhTranslation::getInstance()->getTranslation('chat/chatexport','Active Chats'));

        $i = 2;
        foreach ($data as $time => $item) {

            $key = 0;
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($key, $i, date('Y-m-d H:i:s',$time));

            $key++;
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($key, $i, $item['op_count']);

            $key++;
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($key, $i, $item['pending']);

            $key++;
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($key, $i, $item['active']);

            $i++;
        }

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

        // We'll be outputting an excel file
        header('Content-type: application/vnd.ms-excel');

        // It will be called file.xls
        header('Content-Disposition: attachment; filename="report.xlsx"');

        // Write file to the browser
        $objWriter->save('php://output');
        exit;
    }

    public static function statisticFilter($params)
    {
        if (isset(erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionElasticsearch')->settings['columns'])) {
            foreach (erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionElasticsearch')->settings['columns'] as $columnField => $columnData) {
                $params['filter']['input']->{$columnField} = null;
                $params['filter']['input_form']->{$columnField} = null;
                if (isset($_GET[$columnField]) && !empty(trim($_GET[$columnField]))) {
                    $params['filter']['input']->{$columnField} = $_GET[$columnField];
                    $params['filter']['input_form']->{$columnField} = $_GET[$columnField];
                    if ($columnData['filter_type'] == 'filterstring') {
                        $params['filter']['filter']['filter'][$columnData['field_search']] = (string)$params['filter']['input_form']->{$columnField};
                    } elseif ($columnData['filter_type'] == 'filterrangefloatgt') {
                        $params['filter']['filter']['filtergt'][$columnData['field_search']] = (float)$params['filter']['input_form']->{$columnField};
                    } elseif ($columnData['filter_type'] == 'filterrangefloatlt') {
                        $params['filter']['filter']['filterlt'][$columnData['field_search']] = (float)$params['filter']['input_form']->{$columnField};
                    }
                } elseif (isset($params['uparams'][$columnField]) && !empty(trim($params['uparams'][$columnField]))) {
                    $params['filter']['input']->{$columnField} = $params['uparams'][$columnField];
                    $params['filter']['input_form']->{$columnField} = $params['uparams'][$columnField];
                    if ($columnData['filter_type'] == 'filterstring') {
                        $params['filter']['filter']['filter'][$columnData['field_search']] = (string)$params['filter']['input_form']->{$columnField};
                    } elseif ($columnData['filter_type'] == 'filterrangefloatgt') {
                        $params['filter']['filter']['filtergt'][$columnData['field_search']] = (float)$params['filter']['input_form']->{$columnField};
                    } elseif ($columnData['filter_type'] == 'filterrangefloatlt') {
                        $params['filter']['filter']['filterlt'][$columnData['field_search']] = (float)$params['filter']['input_form']->{$columnField};
                    }
                }
            }
        }
    }

    public static function statisticNumberofchatsdialogsbydepartment($params)
    {
        $elasticSearchHandler = erLhcoreClassElasticClient::getHandler();

        $sparams = array();
        $sparams['index'] = erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionElasticsearch')->settings['index_search'] . '-' . erLhcoreClassModelESChat::$elasticType;
        $sparams['ignore_unavailable'] = true;

        if (isset($_GET['abandoned_chat']) && $_GET['abandoned_chat'] == 1) {
            $params['filter']['filter']['abnd'] = 1;
        }

        if (isset($_GET['dropped_chat']) && $_GET['dropped_chat'] == 1) {
            $params['filter']['filter']['drpd'] = 1;
        }

        if (isset($_GET['transfer_happened']) && $_GET['transfer_happened'] == 1) {
            $sparams['body']['query']['bool']['must'][]['range']['transfer_uid']['gt'] = (int)0;
            $sparams['body']['query']['bool']['must'][]['range']['user_id']['gt'] = (int)0;
            $sparams['body']['query']['bool']['filter']['script']['script'] = "doc['user_id'].value != doc['transfer_uid'].value";
        }

        self::formatFilter($params['filter'], $sparams, array('subject_ids' => 'subject_id'));

        if (! isset($params['filter']['filtergte']['time']) && ! isset($params['filter']['filterlte']['time'])) {
            $ts = mktime(0, 0, 0, date('m'), date('d') - $params['days'], date('y'));
            $sparams['body']['query']['bool']['must'][]['range']['time']['gt'] = $ts * 1000;
            $params['filter']['filtergte']['time'] = $ts;
        }

        $indexSearch = self::getIndexByFilter($params['filter'], erLhcoreClassModelESChat::$elasticType);

        if ($indexSearch != '') {
            $sparams['index'] = $indexSearch;
        }

        $sparams['body']['size'] = 0;
        $sparams['body']['from'] = 0;
        $sparams['body']['aggs']['group_by_country_count']['terms']['field'] = 'dep_id';
        $sparams['body']['aggs']['group_by_country_count']['terms']['size'] = 40;

        $response = $elasticSearchHandler->search($sparams);

        $statsAggr = array();

        foreach ($response['aggregations']['group_by_country_count']['buckets'] as $item) {
            $statsAggr[] = array(
                'number_of_chats' => $item['doc_count'],
                'dep_id' => (trim($item['key']) == '' ? '-' : $item['key'])
            );
        }

        return array(
            'status' => erLhcoreClassChatEventDispatcher::STOP_WORKFLOW,
            'list' => $statsAggr
        );
    }

    public static function statisticNumberofchatsdialogsbyuser($params)
    {
        $elasticSearchHandler = erLhcoreClassElasticClient::getHandler();
        
        $sparams = array();
        $sparams['index'] = erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionElasticsearch')->settings['index_search'] . '-' . erLhcoreClassModelESChat::$elasticType;
        $sparams['ignore_unavailable'] = true;

        if (isset($_GET['abandoned_chat']) && $_GET['abandoned_chat'] == 1) {
            $params['filter']['filter']['abnd'] = 1;
        }

        if (isset($_GET['dropped_chat']) && $_GET['dropped_chat'] == 1) {
            $params['filter']['filter']['drpd'] = 1;
        }

        if (isset($_GET['transfer_happened']) && $_GET['transfer_happened'] == 1) {
            $sparams['body']['query']['bool']['must'][]['range']['transfer_uid']['gt'] = (int)0;
            $sparams['body']['query']['bool']['must'][]['range']['user_id']['gt'] = (int)0;
            $sparams['body']['query']['bool']['filter']['script']['script'] = "doc['user_id'].value != doc['transfer_uid'].value";
        }

        self::formatFilter($params['filter'], $sparams, array('subject_ids' => 'subject_id'));
        
        if (! isset($params['filter']['filtergte']['time']) && ! isset($params['filter']['filterlte']['time'])) {
            $ts = mktime(0, 0, 0, date('m'), date('d') - $params['days'], date('y'));
            $sparams['body']['query']['bool']['must'][]['range']['time']['gt'] = $ts * 1000;
            $params['filter']['filtergte']['time'] = $ts;
        }

        $indexSearch = self::getIndexByFilter($params['filter'], erLhcoreClassModelESChat::$elasticType);

        if ($indexSearch != '') {
            $sparams['index'] = $indexSearch;
        }

        $sparams['body']['size'] = 0;
        $sparams['body']['from'] = 0;
        $sparams['body']['aggs']['group_by_country_count']['terms']['field'] = isset($params['group_field']) ? $params['group_field'] : 'user_id';
        $sparams['body']['aggs']['group_by_country_count']['terms']['size'] = 40;

        $response = $elasticSearchHandler->search($sparams);
        
        $statsAggr = array();
        
        foreach ($response['aggregations']['group_by_country_count']['buckets'] as $item) {
            $statsAggr[] = array(
                'number_of_chats' => $item['doc_count'],
                'user_id' => (trim($item['key']) == '' ? '-' : $item['key'])
            );
        }
        
        return array(
            'status' => erLhcoreClassChatEventDispatcher::STOP_WORKFLOW,
            'list' => $statsAggr
        );
    }
    public static function numberOfChatsDialogsByUserParticipant($params)
    {
        $elasticSearchHandler = erLhcoreClassElasticClient::getHandler();

        $sparams = array();
        $sparams['index'] = erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionElasticsearch')->settings['index_search'] . '-' . erLhcoreClassModelESParticipant::$elasticType;
        $sparams['ignore_unavailable'] = true;


        if (isset($_GET['abandoned_chat']) && $_GET['abandoned_chat'] == 1) {
            $params['filter']['filter']['abnd'] = 1;
        }

        if (isset($_GET['dropped_chat']) && $_GET['dropped_chat'] == 1) {
            $params['filter']['filter']['drpd'] = 1;
        }

        if (isset($_GET['transfer_happened']) && $_GET['transfer_happened'] == 1) {
            $sparams['body']['query']['bool']['must'][]['range']['transfer_uid']['gt'] = (int)0;
            $sparams['body']['query']['bool']['must'][]['range']['user_id']['gt'] = (int)0;
            $sparams['body']['query']['bool']['filter']['script']['script'] = "doc['user_id'].value != doc['transfer_uid'].value";
        }

        self::formatFilter($params['filter'], $sparams, array('subject_ids' => 'subject_id'));

        if (! isset($params['filter']['filtergte']['time']) && ! isset($params['filter']['filterlte']['time'])) {
            $ts = mktime(0, 0, 0, date('m'), date('d') - $params['days'], date('y'));
            $sparams['body']['query']['bool']['must'][]['range']['time']['gt'] = $ts * 1000;
            $params['filter']['filtergte']['time'] = $ts;
        }

        $indexSearch = self::getIndexByFilter($params['filter'], erLhcoreClassModelESParticipant::$elasticType);

        if ($indexSearch != '') {
            $sparams['index'] = $indexSearch;
        }

        $sparams['body']['size'] = 0;
        $sparams['body']['from'] = 0;
        $sparams['body']['aggs']['group_by_participant_count']['terms']['field'] = isset($params['group_field']) ? str_replace('lh_chat_participant.','',$params['group_field']) : 'user_id';
        $sparams['body']['aggs']['group_by_participant_count']['terms']['size'] = 40;

        $response = $elasticSearchHandler->search($sparams);

        $statsAggr = array();

        foreach ($response['aggregations']['group_by_participant_count']['buckets'] as $item) {
            $statsAggr[] = array(
                'number_of_chats' => $item['doc_count'],
                'user_id' => (trim($item['key']) == '' ? '-' : $item['key'])
            );
        }

        return array(
            'status' => erLhcoreClassChatEventDispatcher::STOP_WORKFLOW,
            'list' => $statsAggr
        );
    }

    public static function statisticAvgwaittimeuser($params)
    {
        $elasticSearchHandler = erLhcoreClassElasticClient::getHandler();
        
        $sparams = array();
        $sparams['index'] = erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionElasticsearch')->settings['index_search'] . '-' . erLhcoreClassModelESChat::$elasticType;
        $sparams['ignore_unavailable'] = true;

        $params['filter']['filterlt']['wait_time'] = 600;
        
        self::formatFilter($params['filter'], $sparams, array('subject_ids' => 'subject_id'));
        
        if (! isset($params['filter']['filtergte']['time']) && ! isset($params['filter']['filterlte']['time'])) {
            $ts = mktime(0, 0, 0, date('m'), date('d') - $params['days'], date('y'));
            $sparams['body']['query']['bool']['must'][]['range']['time']['gt'] = $ts * 1000;
            $params['filter']['filtergte']['time'] = $ts;
        }

        if (isset($_GET['abandoned_chat']) && $_GET['abandoned_chat'] == 1) {
            $params['filter']['filter']['abnd'] = 1;
        }

        if (isset($_GET['dropped_chat']) && $_GET['dropped_chat'] == 1) {
            $params['filter']['filter']['drpd'] = 1;
        }

        if (isset($_GET['transfer_happened']) && $_GET['transfer_happened'] == 1) {
            $sparams['body']['query']['bool']['must'][]['range']['transfer_uid']['gt'] = (int)0;
            $sparams['body']['query']['bool']['must'][]['range']['user_id']['gt'] = (int)0;
            $sparams['body']['query']['bool']['filter']['script']['script'] = "doc['user_id'].value != doc['transfer_uid'].value";
        }

        $indexSearch = self::getIndexByFilter($params['filter'], erLhcoreClassModelESChat::$elasticType);

        if ($indexSearch != '') {
            $sparams['index'] = $indexSearch;
        }

        $sparams['body']['size'] = 0;
        $sparams['body']['from'] = 0;
        $sparams['body']['aggs']['group_by_country_count']['terms']['field'] = 'user_id';
        $sparams['body']['aggs']['group_by_country_count']['terms']['size'] = 40;
        $sparams['body']['aggs']['group_by_country_count']['terms']['order']['avg_wait_time'] = 'desc';
        $sparams['body']['aggs']['group_by_country_count']['aggs']['avg_wait_time']['avg']['field'] = 'wait_time';
        
        $response = $elasticSearchHandler->search($sparams);
        
        $statsAggr = array();
        
        foreach ($response['aggregations']['group_by_country_count']['buckets'] as $item) {
            $statsAggr[] = array(
                'avg_wait_time' => $item['avg_wait_time']['value'],
                'user_id' => (trim($item['key']) == '' ? '-' : $item['key'])
            );
        }
        
        return array(
            'status' => erLhcoreClassChatEventDispatcher::STOP_WORKFLOW,
            'list' => $statsAggr
        );
    }

    public static function statisticAverageofchatsdialogsbyuser($params)
    {
        $elasticSearchHandler = erLhcoreClassElasticClient::getHandler();
        
        $sparams = array();
        $sparams['index'] = erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionElasticsearch')->settings['index_search'] . '-' . erLhcoreClassModelESChat::$elasticType;
        $sparams['ignore_unavailable'] = true;

        $params['filter']['filtergt']['chat_duration'] = 0;
        $params['filter']['filterlt']['chat_duration'] = 60*60;
        $params['filter']['filtergt']['user_id'] = 0;
        $params['filter']['filter']['status'] = erLhcoreClassModelChat::STATUS_CLOSED_CHAT;

        if (isset($_GET['abandoned_chat']) && $_GET['abandoned_chat'] == 1) {
            $params['filter']['filter']['abnd'] = 1;
        }

        if (isset($_GET['dropped_chat']) && $_GET['dropped_chat'] == 1) {
            $params['filter']['filter']['drpd'] = 1;
        }

        if (isset($_GET['transfer_happened']) && $_GET['transfer_happened'] == 1) {
            $sparams['body']['query']['bool']['must'][]['range']['transfer_uid']['gt'] = (int)0;
            $sparams['body']['query']['bool']['must'][]['range']['user_id']['gt'] = (int)0;
            $sparams['body']['query']['bool']['filter']['script']['script'] = "doc['user_id'].value != doc['transfer_uid'].value";
        }

        self::formatFilter($params['filter'], $sparams, array('subject_ids' => 'subject_id'));
        
        if (! isset($params['filter']['filtergte']['time']) && ! isset($params['filter']['filterlte']['time'])) {
            $ts = mktime(0, 0, 0, date('m'), date('d') - $params['days'], date('y'));
            $sparams['body']['query']['bool']['must'][]['range']['time']['gt'] = $ts * 1000;
            $params['filter']['filtergte']['time'] = $ts;
        }

        $indexSearch = self::getIndexByFilter($params['filter'], erLhcoreClassModelESChat::$elasticType);

        if ($indexSearch != '') {
            $sparams['index'] = $indexSearch;
        }

        $sparams['body']['size'] = 0;
        $sparams['body']['from'] = 0;
        $sparams['body']['aggs']['group_by_country_count']['terms']['field'] = 'user_id';
        $sparams['body']['aggs']['group_by_country_count']['terms']['size'] = $params['limit'];
        $sparams['body']['aggs']['group_by_country_count']['terms']['order']['avg_chat_duration'] = 'desc';
        $sparams['body']['aggs']['group_by_country_count']['aggs']['avg_chat_duration']['avg']['field'] = 'chat_duration';
        
        $response = $elasticSearchHandler->search($sparams);
        
        $statsAggr = array();
        
        foreach ($response['aggregations']['group_by_country_count']['buckets'] as $item) {
            $statsAggr[] = array(
                'avg_chat_duration' => $item['avg_chat_duration']['value'],
                'user_id' => (trim($item['key']) == '' ? '-' : $item['key'])
            );
        }
        
        return array(
            'status' => erLhcoreClassChatEventDispatcher::STOP_WORKFLOW,
            'list' => $statsAggr
        );
    }

    public static function getTimeZone() {

        if (date_default_timezone_get()) {
           return date_default_timezone_get();
        } else {
            $dateTime = new DateTime("now");
            return $dateTime->getOffset() / 60 / 60;
        }
    }

    public static function statisticGetnumberofchatspermonth($params, $aggr = 'month')
    {
        $numberOfChats = array();
        
        $elasticSearchHandler = erLhcoreClassElasticClient::getHandler();
        
        $sparams = array();
        $sparams['index'] = erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionElasticsearch')->settings['index_search'] . '-' . erLhcoreClassModelESChat::$elasticType;
        $sparams['ignore_unavailable'] = true;
        $sparams['body']['size'] = 0;
        $sparams['body']['from'] = 0;

        if ($aggr != 'weekday') {
            $sparams['body']['aggs']['chats_over_time']['date_histogram']['field'] = 'time';
            $sparams['body']['aggs']['chats_over_time']['date_histogram']['interval'] = $aggr;
            $sparams['body']['aggs']['chats_over_time']['date_histogram']['time_zone'] = self::getTimeZone();
        } else {
            $sparams['body']['aggs']['chats_over_time']['date_histogram']['script'] = "doc['time'].value.dayOfWeek;";
            $sparams['body']['aggs']['chats_over_time']['date_histogram']['interval'] = 1;
            $sparams['body']['aggs']['chats_over_time']['date_histogram']['extended_bounds']['min'] = 1;
            $sparams['body']['aggs']['chats_over_time']['date_histogram']['extended_bounds']['max'] = 7;
            $sparams['body']['aggs']['chats_over_time']['date_histogram']['time_zone'] = self::getTimeZone();
        }

        if (is_array($params['params_execution']['charttypes']) && (in_array('active',$params['params_execution']['charttypes']) || in_array('total_chats',$params['params_execution']['charttypes'])) ) {
            $sparams['body']['aggs']['chats_over_time']['aggs']['status_aggr']['terms']['field'] = 'status';
        }

        if (is_array($params['params_execution']['charttypes']) && in_array('proactivevsdefault', $params['params_execution']['charttypes'])) {
            $sparams['body']['aggs']['chats_over_time']['aggs']['chat_initiator_aggr']['filter']['term']['chat_initiator'] = erLhcoreClassModelChat::CHAT_INITIATOR_DEFAULT;
            $sparams['body']['aggs']['chats_over_time']['aggs']['chat_initiator_proact_aggr']['filter']['bool']['must'][]['term']['chat_initiator'] = erLhcoreClassModelChat::CHAT_INITIATOR_PROACTIVE;
            $sparams['body']['aggs']['chats_over_time']['aggs']['chat_initiator_proact_aggr']['filter']['bool']['must'][]['range']['invitation_id']['gt'] = 0;
            $sparams['body']['aggs']['chats_over_time']['aggs']['chat_initiator_proact_man']['filter']['bool']['must'][]['term']['chat_initiator'] = erLhcoreClassModelChat::CHAT_INITIATOR_PROACTIVE;
            $sparams['body']['aggs']['chats_over_time']['aggs']['chat_initiator_proact_man']['filter']['bool']['must'][]['term']['invitation_id'] = 0;
        }

        if (is_array($params['params_execution']['charttypes']) && in_array('unanswered',$params['params_execution']['charttypes'])){
            $sparams['body']['aggs']['chats_over_time']['aggs']['unanswered_aggr']['filter']['term']['unanswered_chat'] = 1;
        }

        if (isset($_GET['abandoned_chat']) && $_GET['abandoned_chat'] == 1) {
            $params['filter']['filter']['abnd'] = 1;
        }

        if (isset($_GET['dropped_chat']) && $_GET['dropped_chat'] == 1) {
            $params['filter']['filter']['drpd'] = 1;
        }

        if (isset($_GET['transfer_happened']) && $_GET['transfer_happened'] == 1) {
            $sparams['body']['query']['bool']['must'][]['range']['transfer_uid']['gt'] = (int)0;
            $sparams['body']['query']['bool']['must'][]['range']['user_id']['gt'] = (int)0;
            $sparams['body']['query']['bool']['filter']['script']['script'] = "doc['user_id'].value != doc['transfer_uid'].value";
        }

        $paramsOrig = $paramsOrigIndex = $params;
        if ($aggr == 'month') {
            if (!isset($paramsOrig['filter']['filtergte']['time'])) {
                $paramsOrigIndex['filter']['filtergte']['time'] = $paramsOrig['filter']['filtergt']['time'] = time() - (24 * 366 * 3600); // Limit results to one year
             }
        } else {
            if (! isset($paramsOrig['filter']['filtergte']['time']) && ! isset($paramsOrig['filter']['filterlte']['time'])) {
                $paramsOrigIndex['filter']['filtergte']['time'] = $paramsOrig['filter']['filtergt']['time'] = mktime(0, 0, 0, date('m'), date('d') - 31, date('y'));
            }
        }

        $indexSearch = self::getIndexByFilter($paramsOrigIndex['filter'], erLhcoreClassModelESChat::$elasticType);

        if ($indexSearch != '') {
            $sparams['index'] = $indexSearch;
        }

        self::formatFilter($paramsOrig['filter'], $sparams, array('subject_ids' => 'subject_id'));

        $response = $elasticSearchHandler->search($sparams);

        $keyStatus = array(
            erLhcoreClassModelChat::STATUS_CLOSED_CHAT => 'closed',
            erLhcoreClassModelChat::STATUS_ACTIVE_CHAT => 'active',
            erLhcoreClassModelChat::STATUS_OPERATORS_CHAT => 'operators',
            erLhcoreClassModelChat::STATUS_PENDING_CHAT => 'pending',
            erLhcoreClassModelChat::STATUS_BOT_CHAT => 'bot'
        );
        
        $keyStatusInit = array(
            'chatinitdefault',
            'chatinitproact',
            'chatinitmanualinv'
        );

        foreach ($response['aggregations']['chats_over_time']['buckets'] as $bucket) {

            if ($bucket['key'] > 10) {
                $keyDateUnix = $bucket['key'] / 1000;
            } else {
                $keyDateUnix = $bucket['key'];
            }

            if (is_array($params['params_execution']['charttypes']) && (in_array('active',$params['params_execution']['charttypes']) || in_array('total_chats',$params['params_execution']['charttypes']))) {
                $totalChats = 0;
                foreach ($bucket['status_aggr']['buckets'] as $bucketStatus) {
                    if (isset($keyStatus[$bucketStatus['key']])) {
                        $numberOfChats[$keyDateUnix][$keyStatus[$bucketStatus['key']]] = $bucketStatus['doc_count'];
                        $totalChats += $bucketStatus['doc_count'];
                    }
                }
                $numberOfChats[$keyDateUnix]['total_chats'] = $totalChats;
            }

            if (is_array($params['params_execution']['charttypes']) && in_array('unanswered',$params['params_execution']['charttypes'])) {
                $numberOfChats[$keyDateUnix]['unanswered'] = $bucket['unanswered_aggr']['doc_count'];
            }

            if (is_array($params['params_execution']['charttypes']) && in_array('proactivevsdefault', $params['params_execution']['charttypes'])) {
                $numberOfChats[$keyDateUnix]['chatinitdefault'] = $bucket['chat_initiator_aggr']['doc_count'];
                $numberOfChats[$keyDateUnix]['chatinitproact'] = $bucket['chat_initiator_proact_aggr']['doc_count'];
                $numberOfChats[$keyDateUnix]['chatinitmanualinv'] = $bucket['chat_initiator_proact_man']['doc_count'];
            }
            
            foreach ($keyStatus as $mustHave) {
                if (! isset($numberOfChats[$keyDateUnix][$mustHave])) {
                    $numberOfChats[$keyDateUnix][$mustHave] = 0;
                }
            }
            
            foreach ($keyStatusInit as $mustHave) {
                if (! isset($numberOfChats[$keyDateUnix][$mustHave])) {
                    $numberOfChats[$keyDateUnix][$mustHave] = 0;
                }
            }
            
            $numberOfChats[$keyDateUnix]['msg_user'] = 0;
            $numberOfChats[$keyDateUnix]['msg_operator'] = 0;
            $numberOfChats[$keyDateUnix]['msg_system'] = 0;
            $numberOfChats[$keyDateUnix]['msg_bot'] = 0;
        }

        if (is_array($params['params_execution']['charttypes']) &&
            (
                in_array('msgtype', $params['params_execution']['charttypes']) ||
                in_array('msgdelop', $params['params_execution']['charttypes']) ||
                in_array('msgdelbot', $params['params_execution']['charttypes'])
            )
        ) {
            $sparams = array();
            $sparams['index'] = erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionElasticsearch')->settings['index_search'] . '-' . erLhcoreClassModelESMsg::$elasticType;
            $sparams['ignore_unavailable'] = true;
            $sparams['body']['size'] = 0;
            $sparams['body']['from'] = 0;

            if ($aggr != 'weekday') {
                $sparams['body']['aggs']['chats_over_time']['date_histogram']['field'] = 'time';
                $sparams['body']['aggs']['chats_over_time']['date_histogram']['interval'] = $aggr;
                $sparams['body']['aggs']['chats_over_time']['date_histogram']['time_zone'] = self::getTimeZone();
            } else {
                $sparams['body']['aggs']['chats_over_time']['date_histogram']['script'] = "doc['time'].value.dayOfWeek;";
                $sparams['body']['aggs']['chats_over_time']['date_histogram']['interval'] = 1;
                $sparams['body']['aggs']['chats_over_time']['date_histogram']['extended_bounds']['min'] = 1;
                $sparams['body']['aggs']['chats_over_time']['date_histogram']['extended_bounds']['max'] = 7;
                $sparams['body']['aggs']['chats_over_time']['date_histogram']['time_zone'] = self::getTimeZone();
            }

            $sparams['body']['aggs']['chats_over_time']['aggs']['msg_user']['filter']['term']['user_id'] = 0;
            $sparams['body']['aggs']['chats_over_time']['aggs']['msg_system']['filter']['term']['user_id'] = -1;
            $sparams['body']['aggs']['chats_over_time']['aggs']['msg_bot']['filter']['term']['user_id'] = -2;

            // Bot messages delivery status
            $sparams['body']['aggs']['chats_over_time']['aggs']['msg_del_bot']['filter']['term']['user_id'] = -2;
            $sparams['body']['aggs']['chats_over_time']['aggs']['msg_del_bot']['aggs']['msg_del_status']['terms']['field'] = 'del_st';

            // Operator messages delivery status
            $sparams['body']['aggs']['chats_over_time']['aggs']['msg_del_op']['filter']['range']['user_id']['gt'] = 0;
            $sparams['body']['aggs']['chats_over_time']['aggs']['msg_del_op']['aggs']['msg_del_status']['terms']['field'] = 'del_st';

            $paramsOrigIndex = $paramsOrig = $params;

            if ($aggr == 'month') {
                if (!isset($paramsOrig['filter']['filtergte']['time'])) {
                    $paramsOrigIndex['filter']['filtergte']['time'] = $paramsOrig['filter']['filtergt']['time'] = time() - (24 * 366 * 3600); // Limit results to one year
                }
            } else {
                if (!isset($paramsOrig['filter']['filtergte']['time']) && !isset($paramsOrig['filter']['filterlte']['time'])) {
                    $paramsOrigIndex['filter']['filtergte']['time'] = $paramsOrig['filter']['filtergt']['time'] = mktime(0, 0, 0, date('m'), date('d') - 31, date('y'));
                }
            }

            $indexSearch = self::getIndexByFilter($paramsOrigIndex['filter'], erLhcoreClassModelESMsg::$elasticType);

            if ($indexSearch != '') {
                $sparams['index'] = $indexSearch;
            }

            self::formatFilter($paramsOrig['filter'], $sparams);

            $response = $elasticSearchHandler->search($sparams);

            foreach ($response['aggregations']['chats_over_time']['buckets'] as $bucket) {

                if ($bucket['key'] > 10) {
                    $keyDateUnix = $bucket['key'] / 1000;
                } else {
                    $keyDateUnix = $bucket['key'];
                }

                if (isset($numberOfChats[$keyDateUnix])) {
                    $numberOfChats[$keyDateUnix]['msg_operator'] = $bucket['doc_count'] - $bucket['msg_user']['doc_count'] - $bucket['msg_system']['doc_count'] - $bucket['msg_bot']['doc_count'];
                    $numberOfChats[$keyDateUnix]['msg_user'] = $bucket['msg_user']['doc_count'];
                    $numberOfChats[$keyDateUnix]['msg_system'] = $bucket['msg_system']['doc_count'];
                    $numberOfChats[$keyDateUnix]['msg_bot'] = $bucket['msg_bot']['doc_count'];

                    $numberOfChats[$keyDateUnix]['msgdelbot'] = [];
                    $numberOfChats[$keyDateUnix]['msgdelop'] = [];

                    if (isset($bucket['msg_del_op']['msg_del_status']['buckets'])) {
                        foreach ($bucket['msg_del_op']['msg_del_status']['buckets'] as $buckedDelivery) {
                            $numberOfChats[$keyDateUnix]['msgdelop'][$buckedDelivery['key']] = $buckedDelivery['doc_count'];
                        }
                    }

                    if (isset($bucket['msg_del_bot']['msg_del_status']['buckets'])) {
                        foreach ($bucket['msg_del_bot']['msg_del_status']['buckets'] as $buckedDelivery) {
                            $numberOfChats[$keyDateUnix]['msgdelbot'][$buckedDelivery['key']] = $buckedDelivery['doc_count'];
                        }
                    }
                }
            }
        }

        if ($aggr == 'weekday') {
            $sundayData = $numberOfChats[7];
            unset($numberOfChats[7]);
            $numberOfChats[0] = $sundayData;
        }

        return array(
            'status' => erLhcoreClassChatEventDispatcher::STOP_WORKFLOW,
            'list' => $numberOfChats
        );
    }

    /**
     *
     * @param unknown $params            
     * @return multitype:string multitype:
     */
    public static function statisticGetnumberofchatswaittime($params, $aggr = 'month')
    {
        $numberOfChats = array();
        
        $elasticSearchHandler = erLhcoreClassElasticClient::getHandler();
        
        $sparams = array();
        $sparams['index'] = erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionElasticsearch')->settings['index_search'] . '-' . erLhcoreClassModelESChat::$elasticType;
        $sparams['ignore_unavailable'] = true;
        $sparams['body']['size'] = 0;
        $sparams['body']['from'] = 0;

        if ($aggr !== 'weekday') {
            $sparams['body']['aggs']['chats_over_time']['date_histogram']['field'] = 'time';
            $sparams['body']['aggs']['chats_over_time']['date_histogram']['interval'] = 'month';
            $sparams['body']['aggs']['chats_over_time']['date_histogram']['time_zone'] = self::getTimeZone();
        } else {
            $sparams['body']['aggs']['chats_over_time']['date_histogram']['script'] = "doc['time'].value.dayOfWeek;";
            $sparams['body']['aggs']['chats_over_time']['date_histogram']['interval'] = 1;
            $sparams['body']['aggs']['chats_over_time']['date_histogram']['extended_bounds']['min'] = 1;
            $sparams['body']['aggs']['chats_over_time']['date_histogram']['extended_bounds']['max'] = 7;
            $sparams['body']['aggs']['chats_over_time']['date_histogram']['time_zone'] = self::getTimeZone();
        }

        $sparams['body']['aggs']['chats_over_time']['aggs']['avg_wait_time']['avg']['field'] = 'wait_time';

        if (isset($_GET['abandoned_chat']) && $_GET['abandoned_chat'] == 1) {
            $params['filter']['filter']['abnd'] = 1;
        }

        if (isset($_GET['dropped_chat']) && $_GET['dropped_chat'] == 1) {
            $params['filter']['filter']['drpd'] = 1;
        }

        if (isset($_GET['transfer_happened']) && $_GET['transfer_happened'] == 1) {
            $sparams['body']['query']['bool']['must'][]['range']['transfer_uid']['gt'] = (int)0;
            $sparams['body']['query']['bool']['must'][]['range']['user_id']['gt'] = (int)0;
            $sparams['body']['query']['bool']['filter']['script']['script'] = "doc['user_id'].value != doc['transfer_uid'].value";
        }

        $paramsOrig = $params;
        if (!isset($paramsOrig['filter']['filtergte']['time'])) {
            $params['filter']['filtergte']['time']= $paramsOrig['filter']['filtergt']['time'] = time() - (24 * 366 * 3600); // Limit results to one year
        }
        
        $paramsOrig['filter']['filtergt']['wait_time'] = 0;
        $paramsOrig['filter']['filterlt']['wait_time'] = 600;
        
        self::formatFilter($paramsOrig['filter'], $sparams, array('subject_ids' => 'subject_id'));

        $indexSearch = self::getIndexByFilter($params['filter'], erLhcoreClassModelESChat::$elasticType);

        if ($indexSearch != '') {
            $sparams['index'] = $indexSearch;
        }
                
        $response = $elasticSearchHandler->search($sparams);
        
        foreach ($response['aggregations']['chats_over_time']['buckets'] as $bucket) {
            $numberOfChats[$bucket['key'] > 10 ? ($bucket['key'] / 1000) : $bucket['key']] = (int) $bucket['avg_wait_time']['value'];
        }

        if ($aggr == 'weekday') {
            $sundayData = $numberOfChats[7];
            unset($numberOfChats[7]);
            $numberOfChats[0] = $sundayData;
        }

        return array(
            'status' => erLhcoreClassChatEventDispatcher::STOP_WORKFLOW,
            'list' => $numberOfChats
        );
    }

    /**
     *
     * @param unknown $params            
     *
     * @return multitype:string multitype:multitype:unknown
     */
    public static function statisticNumberofmessagesbyuser($params)
    {
        $elasticSearchHandler = erLhcoreClassElasticClient::getHandler();
        
        $sparams = array();
        $sparams['index'] = erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionElasticsearch')->settings['index_search'] . '-' . erLhcoreClassModelESMsg::$elasticType;
        $sparams['ignore_unavailable'] = true;
        $sparams['body']['size'] = 0;
        $sparams['body']['from'] = 0;
        
        $useTimeFilter = ! isset($params['filter']['filtergte']['time']) && ! isset($params['filter']['filterlte']['time']);

        $paramsIndex = $params;
        if ($useTimeFilter == true) {
            $paramsIndex['filter']['filtergte']['time'] = $params['filter']['filtergt']['time'] = mktime(0, 0, 0, date('m'), date('d') - $params['days'], date('y'));
        }

        $params['filter']['filtergt']['user_id'] = 0;

        if (isset($_GET['abandoned_chat']) && $_GET['abandoned_chat'] == 1) {
            $params['filter']['filter']['abnd'] = 1;
        }

        if (isset($_GET['dropped_chat']) && $_GET['dropped_chat'] == 1) {
            $params['filter']['filter']['drpd'] = 1;
        }

        if (isset($_GET['transfer_happened']) && $_GET['transfer_happened'] == 1) {
            $sparams['body']['query']['bool']['must'][]['range']['transfer_uid']['gt'] = (int)0;
            $sparams['body']['query']['bool']['must'][]['range']['user_id']['gt'] = (int)0;
            $sparams['body']['query']['bool']['filter']['script']['script'] = "doc['user_id'].value != doc['transfer_uid'].value";
        }

        self::formatFilter($params['filter'], $sparams);

        $indexSearch = self::getIndexByFilter($paramsIndex['filter'], erLhcoreClassModelESMsg::$elasticType);

        if ($indexSearch != '') {
            $sparams['index'] = $indexSearch;
        }

        $sparams['body']['aggs']['group_by_user']['terms']['field'] = 'user_id';
        $sparams['body']['aggs']['group_by_user']['terms']['size'] = 40;
        
        $response = $elasticSearchHandler->search($sparams);
        
        $items = array();
        foreach ($response['aggregations']['group_by_user']['buckets'] as $item) {
            $items[] = array(
                'number_of_chats' => $item['doc_count'],
                'user_id' => $item['key']
            );
        }
        
        return array(
            'status' => erLhcoreClassChatEventDispatcher::STOP_WORKFLOW,
            'list' => $items
        );
    }

    public static function statisticGetaveragechatduration($params)
    {
        $elasticSearchHandler = erLhcoreClassElasticClient::getHandler();

        $paramsIndex = $params;
        if (!isset($params['filter']['filtergte']['time']) && ! isset($params['filter']['filterlte']['time'])) {
            $paramsIndex['filter']['filtergte']['time'] = $params['filter']['filtergt']['time'] = $dateUnixPast = mktime(0, 0, 0, date('m'), date('d') - $params['days'], date('y'));
        }
        
        $sparams = array();
        $sparams['index'] = erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionElasticsearch')->settings['index_search'] . '-' . erLhcoreClassModelESChat::$elasticType;
        $sparams['ignore_unavailable'] = true;
        $sparams['body']['aggs']['avg_wait_time']['avg']['field'] = 'chat_duration';
        $sparams['body']['size'] = 0;
        $sparams['body']['from'] = 0;
        
        $params['filter']['filtergt']['user_id'] = 0;
        $params['filter']['filtergt']['chat_duration'] = 0;
        $params['filter']['filterlt']['chat_duration'] = 60*60;
        $params['filter']['filter']['status'] = erLhcoreClassModelChat::STATUS_CLOSED_CHAT;

        if (isset($_GET['abandoned_chat']) && $_GET['abandoned_chat'] == 1) {
            $params['filter']['filter']['abnd'] = 1;
        }

        if (isset($_GET['dropped_chat']) && $_GET['dropped_chat'] == 1) {
            $params['filter']['filter']['drpd'] = 1;
        }

        if (isset($_GET['transfer_happened']) && $_GET['transfer_happened'] == 1) {
            $sparams['body']['query']['bool']['must'][]['range']['transfer_uid']['gt'] = (int)0;
            $sparams['body']['query']['bool']['must'][]['range']['user_id']['gt'] = (int)0;
            $sparams['body']['query']['bool']['filter']['script']['script'] = "doc['user_id'].value != doc['transfer_uid'].value";
        }

        self::formatFilter($params['filter'], $sparams, array('subject_ids' => 'subject_id'));

        $indexSearch = self::getIndexByFilter($paramsIndex['filter'], erLhcoreClassModelESChat::$elasticType);

        if ($indexSearch != '') {
            $sparams['index'] = $indexSearch;
        }

        $response = $elasticSearchHandler->search($sparams);
        
        return array(
            'status' => erLhcoreClassChatEventDispatcher::STOP_WORKFLOW,
            'list' => $response['aggregations']['avg_wait_time']['value']
        );
    }

    public static function statisticGetworkloadstatistic($params)
    {
        $elasticSearchHandler = erLhcoreClassElasticClient::getHandler();
        
        $sparams = array();
        $sparams['index'] = erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionElasticsearch')->settings['index_search'] . '-' . erLhcoreClassModelESChat::$elasticType;
        $sparams['ignore_unavailable'] = true;

        if (!isset($params['filter']['filtergte']['time'])) {
            $params['filter']['filtergte']['time'] = mktime(0,0,0,date('m'),date('d')-$params['days'],date('y'));
        }

        if (isset($_GET['abandoned_chat']) && $_GET['abandoned_chat'] == 1) {
            $params['filter']['filter']['abnd'] = 1;
        }

        if (isset($_GET['dropped_chat']) && $_GET['dropped_chat'] == 1) {
            $params['filter']['filter']['drpd'] = 1;
        }

        if (isset($_GET['transfer_happened']) && $_GET['transfer_happened'] == 1) {
            $sparams['body']['query']['bool']['must'][]['range']['transfer_uid']['gt'] = (int)0;
            $sparams['body']['query']['bool']['must'][]['range']['user_id']['gt'] = (int)0;
            $sparams['body']['query']['bool']['filter']['script']['script'] = "doc['user_id'].value != doc['transfer_uid'].value";
        }

        $diffDays = ((isset($params['filter']['filterlte']['time']) ? $params['filter']['filterlte']['time'] : time())-$params['filter']['filtergte']['time'])/(24*3600);

        self::formatFilter($params['filter'], $sparams, array('subject_ids' => 'subject_id'));
        
        $sparams['body']['size'] = 0;
        $sparams['body']['from'] = 0;
        $sparams['body']['aggs']['group_by_hour']['terms']['field'] = 'hour';
        $sparams['body']['aggs']['group_by_hour']['terms']['size'] = 48;

        $indexSearch = self::getIndexByFilter($params['filter'], erLhcoreClassModelESChat::$elasticType);

        if ($indexSearch != '') {
            $sparams['index'] = $indexSearch;
        }

        $response = $elasticSearchHandler->search($sparams);
        
        $numberOfChats['total'] = array_fill(1, 23, 0);
        $numberOfChats['byday'] = array_fill(1, 23, 0);

        $dateTime = new DateTime("now");
        $utcAdjust = $dateTime->getOffset() / 60 / 60; // Hours are stored in UTC format. We need to adjust filters

        foreach ($response['aggregations']['group_by_hour']['buckets'] as $item) {
            $hourAdjusted = $item['key'] + $utcAdjust;

            if ($hourAdjusted < 0){
                $hourAdjusted = 24 + $hourAdjusted;
            }

            if ($hourAdjusted > 23) {
                $hourAdjusted = $hourAdjusted - 24;
            }

            $numberOfChats['total'][$hourAdjusted] = $item['doc_count'];
            $numberOfChats['byday'][$hourAdjusted] = $item['doc_count']/$diffDays;
        }

        ksort($numberOfChats, SORT_NUMERIC);
        
        return array(
            'status' => erLhcoreClassChatEventDispatcher::STOP_WORKFLOW,
            'list' => $numberOfChats
        );
    }

    public static function statisticGetnumberofchatsperday($params)
    {
        return self::statisticGetnumberofchatspermonth($params, 'day');
    }

    public static function statisticGetnumberofchatsperweekday($params)
    {
        return self::statisticGetnumberofchatspermonth($params, 'weekday');
    }

    public static function getNumberOfChatsWaitTimePerWeekDay($params)
    {
        return self::statisticGetnumberofchatswaittime($params, 'weekday');
    }

    /**
     *
     * @todo rewrite using aggregation
     *      
     * @param array $params            
     *
     * @return multitype:string Ambigous <number, unknown, boolean>
     */
    public static function statisticGetlast24hstatistic($params)
    {
        $sparams = array();
        $sparams['index'] = erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionElasticsearch')->settings['index_search'] . '-' . erLhcoreClassModelESChat::$elasticType;

        $dateIndexFilter = array();

        if (isset($params['filter']['filtergte']['time'])) {
            $dateIndexFilter['date_index']['gte'] = $params['filter']['filtergte']['time'];
        }

        $sparamsItem = $sparams;
        $paramsOrig = $params;
        self::formatFilter($paramsOrig['filter'], $sparamsItem, array('subject_ids' => 'subject_id'));
        $numberOfChats['totalchats'] = erLhcoreClassModelESChat::getCount($sparamsItem, $dateIndexFilter);
        
        $sparamsItem = $sparams;
        $paramsOrig = $params;
        $paramsOrig['filter']['filter']['status'] = erLhcoreClassModelChat::STATUS_PENDING_CHAT;
        self::formatFilter($paramsOrig['filter'], $sparamsItem, array('subject_ids' => 'subject_id'));
        $numberOfChats['totalpendingchats'] = erLhcoreClassModelESChat::getCount($sparamsItem, $dateIndexFilter);
        
        $sparamsItem = $sparams;
        $paramsOrig = $params;
        $paramsOrig['filter']['filter']['status'] = erLhcoreClassModelChat::STATUS_ACTIVE_CHAT;
        self::formatFilter($paramsOrig['filter'], $sparamsItem, array('subject_ids' => 'subject_id'));
        $numberOfChats['total_active_chats'] = erLhcoreClassModelESChat::getCount($sparamsItem, $dateIndexFilter);
        
        $sparamsItem = $sparams;
        $paramsOrig = $params;
        $paramsOrig['filter']['filter']['status'] = erLhcoreClassModelChat::STATUS_CLOSED_CHAT;
        self::formatFilter($paramsOrig['filter'], $sparamsItem, array('subject_ids' => 'subject_id'));
        $numberOfChats['total_closed_chats'] = erLhcoreClassModelESChat::getCount($sparamsItem, $dateIndexFilter);
        
        $sparamsItem = $sparams;
        $paramsOrig = $params;
        $paramsOrig['filter']['filter']['unanswered_chat'] = 1;
        self::formatFilter($paramsOrig['filter'], $sparamsItem, array('subject_ids' => 'subject_id'));
        $numberOfChats['total_unanswered_chat'] = erLhcoreClassModelESChat::getCount($sparamsItem, $dateIndexFilter);
        
        $sparamsItem = $sparams;
        $paramsOrig = $params;
        $paramsOrig['filter']['filter']['status'] = erLhcoreClassModelChat::STATUS_CHATBOX_CHAT;
        self::formatFilter($paramsOrig['filter'], $sparamsItem, array('subject_ids' => 'subject_id'));
        $numberOfChats['chatbox_chats'] = erLhcoreClassModelESChat::getCount($sparamsItem, $dateIndexFilter);
        
        $sparamsItem = $sparams;
        $paramsOrig = $params;
        self::formatFilter($paramsOrig['filter'], $sparamsItem);
        $sparamsItem['index'] = erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionElasticsearch')->settings['index_search'] . '-' . erLhcoreClassModelESMsg::$elasticType;
        $numberOfChats['ttmall'] = erLhcoreClassModelESMsg::getCount($sparamsItem, $dateIndexFilter);
        
        $sparamsItem = $sparams;
        $paramsOrig = $params;
        $paramsOrig['filter']['filter']['user_id'] = 0;
        self::formatFilter($paramsOrig['filter'], $sparamsItem);
        $sparamsItem['index'] = erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionElasticsearch')->settings['index_search'] . '-' . erLhcoreClassModelESMsg::$elasticType;
        $numberOfChats['ttmvis'] = erLhcoreClassModelESMsg::getCount($sparamsItem, $dateIndexFilter);
        
        $sparamsItem = $sparams;
        $paramsOrig = $params;
        $paramsOrig['filter']['filterin']['user_id'] = array(
            - 1,
            - 2
        );
        self::formatFilter($paramsOrig['filter'], $sparamsItem);
        $sparamsItem['index'] = erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionElasticsearch')->settings['index_search'] . '-' . erLhcoreClassModelESMsg::$elasticType;
        $numberOfChats['ttmsys'] = erLhcoreClassModelESMsg::getCount($sparamsItem, $dateIndexFilter);

        $numberOfChats['ttmop'] = $numberOfChats['ttmall'] - $numberOfChats['ttmvis'] - $numberOfChats['ttmsys'];
        
        return array(
            'status' => erLhcoreClassChatEventDispatcher::STOP_WORKFLOW,
            'list' => $numberOfChats
        );
    }

    public static function statisticGetnumberofchatswaittimeperday($params)
    {
        $elasticSearchHandler = erLhcoreClassElasticClient::getHandler();

        if (isset($_GET['abandoned_chat']) && $_GET['abandoned_chat'] == 1) {
            $params['filter']['filter']['abnd'] = 1;
        }

        if (isset($_GET['dropped_chat']) && $_GET['dropped_chat'] == 1) {
            $params['filter']['filter']['drpd'] = 1;
        }

        $numberOfChats = array();
        
        $sparams = array();
        $sparams['index'] = erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionElasticsearch')->settings['index_search'] . '-' . erLhcoreClassModelESChat::$elasticType;
        $sparams['ignore_unavailable'] = true;

        if (isset($_GET['transfer_happened']) && $_GET['transfer_happened'] == 1) {
            $sparams['body']['query']['bool']['must'][]['range']['transfer_uid']['gt'] = (int)0;
            $sparams['body']['query']['bool']['must'][]['range']['user_id']['gt'] = (int)0;
            $sparams['body']['query']['bool']['filter']['script']['script'] = "doc['user_id'].value != doc['transfer_uid'].value";
        }

        self::formatFilter($params['filter'], $sparams, array('subject_ids' => 'subject_id'));
        
        if (! isset($params['filter']['filtergte']['time']) && ! isset($params['filter']['filterlte']['time'])) {
            $ts = mktime(0, 0, 0, date('m'), date('d') - 31, date('y'));
            $sparams['body']['query']['bool']['must'][]['range']['time']['gt'] = $ts * 1000;
            $params['filter']['filtergte']['time'] = $ts;
        }
        
        $sparams['body']['size'] = 0;
        $sparams['body']['from'] = 0;
        $sparams['body']['aggs']['chats_over_time']['date_histogram']['field'] = 'time';
        $sparams['body']['aggs']['chats_over_time']['date_histogram']['interval'] = 'day';
        $sparams['body']['aggs']['chats_over_time']['aggs']['avg_wait_time']['avg']['field'] = 'wait_time';

        $sparams['body']['aggs']['chats_over_time']['date_histogram']['time_zone'] = self::getTimeZone();
        
        $sparams['body']['query']['bool']['must'][]['range']['wait_time']['gt'] = 0;
        $sparams['body']['query']['bool']['must'][]['range']['wait_time']['lt'] = 600;

        $indexSearch = self::getIndexByFilter($params['filter'], erLhcoreClassModelESChat::$elasticType);

        if ($indexSearch != '') {
            $sparams['index'] = $indexSearch;
        }

        $items = $elasticSearchHandler->search($sparams);
        
        $numberOfChats = array();
        
        if (isset($items['aggregations']['chats_over_time']['buckets'])) {
            foreach ($items['aggregations']['chats_over_time']['buckets'] as $item) {
                $numberOfChats[$item['key'] / 1000] = (int) $item['avg_wait_time']['value'];
            }
        }
        
        return array(
            'status' => erLhcoreClassChatEventDispatcher::STOP_WORKFLOW,
            'list' => $numberOfChats
        );
    }

    public static function statisticGetperformancestatistic($params)
    {
        $stats = array(
            'rows' => array(),
            'total_chats' => 0,
            'total_aband_chats' => 0
        );
        
        $elasticSearchHandler = erLhcoreClassElasticClient::getHandler();
        
        // Chat statistic aggregation
        $sparams = array();
        $sparams['index'] = erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionElasticsearch')->settings['index_search'] . '-' . erLhcoreClassModelESChat::$elasticType;
        $sparams['ignore_unavailable'] = true;

        if (isset($_GET['abandoned_chat']) && $_GET['abandoned_chat'] == 1) {
            $params['filter']['filter']['abnd'] = 1;
        }

        if (isset($_GET['dropped_chat']) && $_GET['dropped_chat'] == 1) {
            $params['filter']['filter']['drpd'] = 1;
        }

        if (isset($_GET['transfer_happened']) && $_GET['transfer_happened'] == 1) {
            $sparams['body']['query']['bool']['must'][]['range']['transfer_uid']['gt'] = (int)0;
            $sparams['body']['query']['bool']['must'][]['range']['user_id']['gt'] = (int)0;
            $sparams['body']['query']['bool']['filter']['script']['script'] = "doc['user_id'].value != doc['transfer_uid'].value";
        }

        self::formatFilter($params['filter'], $sparams, array('subject_ids' => 'subject_id'));

        // Add default date range filter
        if (! isset($params['filter']['filtergte']['time']) && ! isset($params['filter']['filterlte']['time'])) {
            $ts = mktime(0, 0, 0, date('m'), date('d') - 31, date('y'));
            $sparams['body']['query']['bool']['must'][]['range']['time']['gt'] = $ts * 1000;
            $params['filter']['filtergte']['time'] = $ts;
        }

        $sparams['body']['size'] = 0;
        $sparams['body']['from'] = 0;
        
        $sparams['body']['aggs']['chat_count']['range']['field'] = 'wait_time';
        
        $filterParams = $params['filter_params'];
        
        $dateTime = new DateTime("now");
        $utcAdjust = $dateTime->getOffset() / 60 / 60; // Hours are stored in UTC format. We need to adjust filters
               
        if (isset($filterParams['input']->timefrom_include_hours) && is_numeric($filterParams['input']->timefrom_include_hours)){
            $filterParams['input']->timefrom_include_hours = $filterParams['input']->timefrom_include_hours - $utcAdjust;
            
            if ($filterParams['input']->timefrom_include_hours < 0) {
                $filterParams['input']->timefrom_include_hours = 24 + $filterParams['input']->timefrom_include_hours;
            }
            
            if ($filterParams['input']->timefrom_include_hours > 23) {
                $filterParams['input']->timefrom_include_hours = $filterParams['input']->timefrom_include_hours - 24;
            }
        }

        if (isset($filterParams['input']->timeto_include_hours) && is_numeric($filterParams['input']->timeto_include_hours)){
            $filterParams['input']->timeto_include_hours = $filterParams['input']->timeto_include_hours - $utcAdjust;
            
            if ($filterParams['input']->timeto_include_hours < 0) {
                $filterParams['input']->timeto_include_hours = 24 + $filterParams['input']->timeto_include_hours;
            }
            
            if ($filterParams['input']->timeto_include_hours > 23) {
                $filterParams['input']->timeto_include_hours = $filterParams['input']->timeto_include_hours - 24;
            }
        }

        // Include fixed hours range
        if ((isset($filterParams['input']->timefrom_include_hours) && is_numeric($filterParams['input']->timefrom_include_hours)) && (isset($filterParams['input']->timeto_include_hours) && is_numeric($filterParams['input']->timeto_include_hours))) {
   
            if ($filterParams['input']->timefrom_include_hours <= $filterParams['input']->timeto_include_hours){
                $sparams['body']['query']['bool']['must'][]['range']['hour']['gte'] = (int)$filterParams['input']->timefrom_include_hours;
                $sparams['body']['query']['bool']['must'][]['range']['hour']['lt'] = (int)$filterParams['input']->timeto_include_hours;
            } else {
                $sparams['body']['query']['bool']['should'][]['range']['hour']['gte'] = (int)$filterParams['input']->timefrom_include_hours;
                $sparams['body']['query']['bool']['should'][]['range']['hour']['lt'] = (int)$filterParams['input']->timeto_include_hours;
                $sparams['body']['query']['bool']['minimum_should_match'] = 1; // Minimum one condition should be matched
            }
        
        } elseif (isset($filterParams['input']->timeto_include_hours) && is_numeric($filterParams['input']->timeto_include_hours)) {            
            $sparams['body']['query']['bool']['must'][]['range']['hour']['lt'] = (int)$filterParams['input']->timeto_include_hours;
        } elseif (isset($filterParams['input']->timefrom_include_hours) && is_numeric($filterParams['input']->timefrom_include_hours)) {
            $sparams['body']['query']['bool']['must'][]['range']['hour']['gte'] = (int)$filterParams['input']->timefrom_include_hours;
        }

        // Append extension custom aggregation
        erLhcoreClassChatEventDispatcher::getInstance()->dispatch('elasticsearch.getperformancestatistic', array(
            'sparams' => & $sparams,
        ));

        foreach ($params['ranges'] as $rangeData) {
            $rangeFilter = array();
            
            if ($rangeData['from'] !== false) {
                $rangeFilter['from'] = $rangeData['from'];
            }
            
            if ($rangeData['to'] !== false) {
                $rangeFilter['to'] = $rangeData['to'] + 1;
            }
            
            $sparams['body']['aggs']['chat_count']['range']['ranges'][] = $rangeFilter;
        }
        
        $sparams['body']['aggs']['chat_count']['aggs']['abandoned_chats']['filter']['bool']['must'][]['term']['user_id'] = 0;
        $sparams['body']['aggs']['chat_count']['aggs']['abandoned_chats']['filter']['bool']['must'][]['terms']['status_sub'] = array(erLhcoreClassModelChat::STATUS_SUB_USER_CLOSED_CHAT, erLhcoreClassModelChat::STATUS_SUB_SURVEY_COMPLETED);

        $indexSearch = self::getIndexByFilter($params['filter'], erLhcoreClassModelESChat::$elasticType);

        if ($indexSearch != '') {
            $sparams['index'] = $indexSearch;
        }

        $result = $elasticSearchHandler->search($sparams, array('subject_ids' => 'subject_id'));
        
        foreach ($result['aggregations']['chat_count']['buckets'] as $key => $bucket) {
            
            $stats['rows'][] = array(
                'from' => $bucket['from'],
                'to' => isset($bucket['to']) ? ($bucket['to'] - 1) : false,
                'tt' => $params['ranges'][$key]['tt'],
                'started' => $bucket['doc_count'],
                'abandoned' => $bucket['abandoned_chats']['doc_count']
            );
            
            $stats['total_chats'] += $bucket['doc_count'];
            $stats['total_aband_chats'] += $bucket['abandoned_chats']['doc_count'];
        }
        
        return array(
            'status' => erLhcoreClassChatEventDispatcher::STOP_WORKFLOW,
            'list' => $stats
        );
    }

    public static function statisticGetagentstatistic($params)
    {

        $elasticSearchHandler = erLhcoreClassElasticClient::getHandler();

        // Chat statistic aggregation
        $sparams = array();
        $sparams['index'] = erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionElasticsearch')->settings['index_search'] . '-' . erLhcoreClassModelESChat::$elasticType;;
        $sparams['ignore_unavailable'] = true;

        if (!empty($params['user_filter'])) {
            $params['filter']['filterin']['user_id'] = $params['user_filter'];
        }

        if (isset($_GET['abandoned_chat']) && $_GET['abandoned_chat'] == 1) {
            $params['filter']['filter']['abnd'] = 1;
        }

        if (isset($_GET['dropped_chat']) && $_GET['dropped_chat'] == 1) {
            $params['filter']['filter']['drpd'] = 1;
        }

        if (isset($_GET['transfer_happened']) && $_GET['transfer_happened'] == 1) {
            $sparams['body']['query']['bool']['must'][]['range']['transfer_uid']['gt'] = (int)0;
            $sparams['body']['query']['bool']['must'][]['range']['user_id']['gt'] = (int)0;
            $sparams['body']['query']['bool']['filter']['script']['script'] = "doc['user_id'].value != doc['transfer_uid'].value";
        }

        self::formatFilter($params['filter'], $sparams, array('subject_ids' => 'subject_id'));

        $paramsIndex = $params;
        if (! isset($params['filter']['filtergte']['time']) && ! isset($params['filter']['filterlte']['time'])) {
            $ts = mktime(0, 0, 0, date('m'), date('d') - $params['days'], date('y'));
            $sparams['body']['query']['bool']['must'][]['range']['time']['gt'] = $ts * 1000;
            $paramsIndex['filter']['filtergte']['time'] = $ts;
        }

        $indexSearch = self::getIndexByFilter($paramsIndex['filter'], erLhcoreClassModelESChat::$elasticType);

        if ($indexSearch != '') {
            $sparams['index'] = $indexSearch;
        }

        $sparams['body']['size'] = 0;
        $sparams['body']['from'] = 0;
        $sparams['body']['aggs']['group_by_user']['terms']['field'] = 'user_id';
        $sparams['body']['aggs']['group_by_user']['terms']['size'] = 1000;
        
        // $filterOnline['filter']['usaccept'] = 0; erLhcoreClassChatStatistic::numberOfChatsDialogsByUser(30,$filterOnline);
        $sparams['body']['aggs']['group_by_user']['aggs']['us_accept']['filter']['term']['usaccept'] = 0;

        // Subject aggregation
        if (isset($params['filter_original']['filterin']['subject_id']) && !empty($params['filter_original']['filterin']['subject_id'])) {
            $field = 'subject_id';

            $statusWorkflow = erLhcoreClassChatEventDispatcher::getInstance()->dispatch('elasticsearch.getsubjectsstatistic_field', array('field' => $field));

            if ($statusWorkflow !== false) {
                $field = $statusWorkflow['field'];
            }

            $sparams['body']['aggs']['group_by_user']['aggs']['subject_id']['aggs']['by_subject']['terms']['field'] = $field;
            $sparams['body']['aggs']['group_by_user']['aggs']['subject_id']['filter']['terms'][$field] = $params['filter_original']['filterin']['subject_id'];
        }

        // totalHoursOfOnlineDialogsByUser
        $sparams['body']['aggs']['group_by_user']['aggs']['closed_chats']['filter']['bool']['must'][]['term']['status'] = erLhcoreClassModelChat::STATUS_CLOSED_CHAT;
        $sparams['body']['aggs']['group_by_user']['aggs']['closed_chats']['filter']['bool']['must'][]['range']['chat_duration']['gt'] = 0;
        $sparams['body']['aggs']['group_by_user']['aggs']['closed_chats']['filter']['bool']['must'][]['range']['chat_duration']['lt'] = 60*60;

        // Sum
        $sparams['body']['aggs']['group_by_user']['aggs']['closed_chats']['aggs']['chat_duration_sum']['sum']['field'] = 'chat_duration';
        
        // getAverageChatduration
        $sparams['body']['aggs']['group_by_user']['aggs']['closed_chats']['aggs']['chat_duration_avg']['avg']['field'] = 'chat_duration';
        
        // avgWaitTimeFilter
        $sparams['body']['aggs']['group_by_user']['aggs']['avg_wait_time_filter']['filter']['range']['wait_time']['lt'] = 600;
        $sparams['body']['aggs']['group_by_user']['aggs']['avg_wait_time_filter']['aggs']['wait_time']['avg']['field'] = 'wait_time';

        erLhcoreClassChatEventDispatcher::getInstance()->dispatch('elasticsearch.getagentstatistic', array(
            'sparams' => & $sparams
        ));

        $result = $elasticSearchHandler->search($sparams);

        $usersStats = array();
        foreach ($result['aggregations']['group_by_user']['buckets'] as $bucket) {

            $subjectStats = array();

            if (isset($bucket['subject_id']['by_subject']['buckets'])){
                foreach ($bucket['subject_id']['by_subject']['buckets'] as $bucketSubject){
                    $subjectStats[] = array(
                        'number_of_chats' => $bucketSubject['doc_count'],
                        'subject_id' => $bucketSubject['key'],
                        'perc' => round($bucketSubject['doc_count']/$bucket['doc_count']*10000) / 100
                    );
                }
            }

            $statsValue = array(
                'total_chats' => $bucket['doc_count'],
                'total_chats_usaccept' => $bucket['us_accept']['doc_count'],
                'chat_duration_sum' => $bucket['closed_chats']['chat_duration_sum']['value'],
                'chat_duration_avg' => $bucket['closed_chats']['chat_duration_avg']['value'],
                'wait_time' => $bucket['avg_wait_time_filter']['wait_time']['value'],
                'subject_stats' => $subjectStats
            );

            // Append extension custom aggregation
            erLhcoreClassChatEventDispatcher::getInstance()->dispatch('elasticsearch.getagentstatistic_chats_value', array(
                'bucket' => & $bucket,
                'stats_value' => & $statsValue
            ));

            $usersStats[$bucket['key']] = $statsValue;
        }

        // Mails aggregation
        $sparams = array();
        $sparams['index'] = erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionElasticsearch')->settings['index_search'] . '-' . erLhcoreClassModelESOnlineSession::$elasticType;
        $sparams['ignore_unavailable'] = true;

        $userIdFilter = array_keys($usersStats);
        if (!empty($userIdFilter)) {
            $params['filter']['filterin']['user_id'] = $userIdFilter;
        } else {
            return array(
                'status' => erLhcoreClassChatEventDispatcher::STOP_WORKFLOW,
                'list' => array()
            );
        }

        self::formatFilter($params['filter'], $sparams);

        $paramsIndex = $params;

        if (! isset($params['filter']['filtergte']['time']) && ! isset($params['filter']['filterlte']['time'])) {
            $ts = mktime(0, 0, 0, date('m'), date('d') - $params['days'], date('y'));
            $sparams['body']['query']['bool']['must'][]['range']['time']['gt'] = $ts * 1000;
            $paramsIndex['filter']['filtergte']['time'] = $ts;
        }

        $indexSearch = self::getIndexByFilter($paramsIndex['filter'], erLhcoreClassModelESMail::$elasticType);

        if ($indexSearch != '') {
            $sparams['index'] = $indexSearch;
        }

        $sparams['body']['size'] = 0;
        $sparams['body']['from'] = 0;
        $sparams['body']['aggs']['group_by_user']['terms']['field'] = 'user_id';
        $sparams['body']['aggs']['group_by_user']['terms']['size'] = 1000;
        $sparams['body']['aggs']['group_by_user']['aggs']['response_type']['terms']['field'] = 'response_type';
        $sparams['body']['aggs']['group_by_user']['aggs']['response_type']['terms']['size'] = 1000;

        $result = $elasticSearchHandler->search($sparams);

        foreach ($result['aggregations']['group_by_user']['buckets'] as $bucket) {
            foreach ($bucket['response_type']['buckets'] as $bucketStat) {
                $usersStats[$bucket['key']]['mail_statistic_'.$bucketStat['key']] = $bucketStat['doc_count'];
            }
        }

        // Online hours aggregration
        $sparams = array();
        $sparams['index'] = erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionElasticsearch')->settings['index_search'] . '-' . erLhcoreClassModelESOnlineSession::$elasticType;
        $sparams['ignore_unavailable'] = true;

        $userIdFilter = array_keys($usersStats);
        if (!empty($userIdFilter)) {
            $params['filter']['filterin']['user_id'] = $userIdFilter;
        } else {
            return array(
                'status' => erLhcoreClassChatEventDispatcher::STOP_WORKFLOW,
                'list' => array()
            );
        }

        // Remove department filter
        $filterOnlineHours = $params['filter'];
        if (isset($filterOnlineHours['filter']['dep_id'])) {
            unset($filterOnlineHours['filter']['dep_id']);
        }
        
        if (isset($filterOnlineHours['filterin']['dep_id'])) {
            unset($filterOnlineHours['filterin']['dep_id']);
        }
        
        self::formatFilter($filterOnlineHours, $sparams);

        $paramsIndex = $params;

        if (! isset($params['filter']['filtergte']['time']) && ! isset($params['filter']['filterlte']['time'])) {
            $ts = mktime(0, 0, 0, date('m'), date('d') - $params['days'], date('y'));
            $sparams['body']['query']['bool']['must'][]['range']['time']['gt'] = $ts * 1000;
            $paramsIndex['filter']['filtergte']['time'] = $ts;
        }

        $indexSearch = self::getIndexByFilter($paramsIndex['filter'], erLhcoreClassModelESOnlineSession::$elasticType);

        if ($indexSearch != '') {
            $sparams['index'] = $indexSearch;
        }

        $sparams['body']['size'] = 0;
        $sparams['body']['from'] = 0;
        $sparams['body']['aggs']['group_by_user']['terms']['field'] = 'user_id';
        $sparams['body']['aggs']['group_by_user']['terms']['size'] = 1000;
        $sparams['body']['aggs']['group_by_user']['aggs']['duration_sum']['sum']['field'] = 'duration';

        $result = $elasticSearchHandler->search($sparams);
        
        foreach ($result['aggregations']['group_by_user']['buckets'] as $bucket) {
            $usersStats[$bucket['key']]['online_hours'] = $bucket['duration_sum']['value'];
        }

        // Participant aggregation
        $sparams = array();
        $sparams['index'] = erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionElasticsearch')->settings['index_search'] . '-' . erLhcoreClassModelESParticipant::$elasticType;
        $sparams['ignore_unavailable'] = true;

        $userIdFilter = array_keys($usersStats);
        if (!empty($userIdFilter)) {
            $params['filter']['filterin']['user_id'] = $userIdFilter;
        } else {
            return array(
                'status' => erLhcoreClassChatEventDispatcher::STOP_WORKFLOW,
                'list' => array()
            );
        }

        // Remove department filter
        $filterParticipant = $params['filter'];

        self::formatFilter($filterParticipant, $sparams);

        $paramsIndex = $params;

        if (! isset($params['filter']['filtergte']['time']) && ! isset($params['filter']['filterlte']['time'])) {
            $ts = mktime(0, 0, 0, date('m'), date('d') - $params['days'], date('y'));
            $sparams['body']['query']['bool']['must'][]['range']['time']['gt'] = $ts * 1000;
            $paramsIndex['filter']['filtergte']['time'] = $ts;
        }

        $indexSearch = self::getIndexByFilter($paramsIndex['filter'], erLhcoreClassModelESParticipant::$elasticType);

        if ($indexSearch != '') {
            $sparams['index'] = $indexSearch;
        }

        $sparams['body']['size'] = 0;
        $sparams['body']['from'] = 0;
        $sparams['body']['aggs']['group_by_user']['terms']['field'] = 'user_id';
        $sparams['body']['aggs']['group_by_user']['terms']['size'] = 1000;
        $sparams['body']['aggs']['group_by_user']['aggs']['duration_sum']['sum']['field'] = 'duration';

        $result = $elasticSearchHandler->search($sparams);

        foreach ($result['aggregations']['group_by_user']['buckets'] as $bucket) {
            $usersStats[$bucket['key']]['total_chats_participant'] = $bucket['doc_count'];
            $usersStats[$bucket['key']]['total_hours_participant'] = $bucket['duration_sum']['value'];
        }


        // Default logic
        $list = array();

        // Set again user List
        $params['user_list'] = erLhcoreClassModelUser::getUserList(array('filterin' => array('id' => $userIdFilter)));

        foreach ($params['user_list'] as $user) {
            $agentName = trim($user->name .' '. $user->surname);
            $numberOfChats = isset($usersStats[$user->id]['total_chats']) ? $usersStats[$user->id]['total_chats'] : 0;


            $numberOfChatsOnline = isset($usersStats[$user->id]['total_chats_usaccept']) ? $usersStats[$user->id]['total_chats_usaccept'] : 0;
            
            $totalHoursOnline = isset($usersStats[$user->id]['online_hours']) ? $usersStats[$user->id]['online_hours'] : 0;
            
            $totalHoursOnlineCount = erLhcoreClassChatStatistic::formatHours($totalHoursOnline);
            
            if ($totalHoursOnlineCount > 1) {
                $aveNumber = round($numberOfChatsOnline / $totalHoursOnlineCount, 2);
            } else {
                $aveNumber = $numberOfChatsOnline;
            }

            $avgWaitTime = isset($usersStats[$user->id]['wait_time']) ? $usersStats[$user->id]['wait_time'] : 0;
            $totalHours = isset($usersStats[$user->id]['chat_duration_sum']) ? $usersStats[$user->id]['chat_duration_sum'] : 0;
            $avgDuration = isset($usersStats[$user->id]['chat_duration_avg']) ? $usersStats[$user->id]['chat_duration_avg'] : 0;

            // Participant data
            $numberOfChatsParticipant = isset($usersStats[$user->id]['total_chats_participant']) ? $usersStats[$user->id]['total_chats_participant'] : 0;
            $totalHoursParticipant = isset($usersStats[$user->id]['total_hours_participant']) ? $usersStats[$user->id]['total_hours_participant'] : 0;

            if ($totalHoursOnlineCount > 1) {
                $aveNumberParticipant = round($numberOfChatsParticipant / $totalHoursOnlineCount, 2);
            } else {
                $aveNumberParticipant = $numberOfChatsParticipant;
            }

            $statsRecord = array(
                'agentName' => $agentName,
                'userId' => $user->id,
                'numberOfChats' => $numberOfChats,
                'numberOfChatsOnline' => $numberOfChatsOnline,
                'totalHours' => $totalHours,
                'totalHours_front' => ($totalHours > 0 ? erLhcoreClassChat::formatSeconds($totalHours) : '0 s.'),
                'totalHoursOnline' => $totalHoursOnline,
                'totalHoursOnline_front' => ($totalHoursOnline > 0 ? erLhcoreClassChat::formatSeconds($totalHoursOnline) : '0 s.'),
                'aveNumber' => $aveNumber,
                'avgWaitTime' => $avgWaitTime,
                'avgWaitTime_front' => ($avgWaitTime > 0 ? erLhcoreClassChat::formatSeconds($avgWaitTime) : ' 0 s.'),
                'avgChatLength' => ($avgDuration > 0 ? erLhcoreClassChat::formatSeconds($avgDuration) : '0 s.'),
                'avgChatLengthSeconds' => $avgDuration,
                'subject_stats' => (isset($usersStats[$user->id]['subject_stats']) ? $usersStats[$user->id]['subject_stats'] : array()),
                'mail_statistic_0' => (isset($usersStats[$user->id]['mail_statistic_0']) ? $usersStats[$user->id]['mail_statistic_0'] : 0),
                'mail_statistic_1' => (isset($usersStats[$user->id]['mail_statistic_1']) ? $usersStats[$user->id]['mail_statistic_1'] : 0),
                'mail_statistic_2' => (isset($usersStats[$user->id]['mail_statistic_2']) ? $usersStats[$user->id]['mail_statistic_2'] : 0),
                'mail_statistic_3' => (isset($usersStats[$user->id]['mail_statistic_3']) ? $usersStats[$user->id]['mail_statistic_3'] : 0),

                'aveNumberParticipant' => $aveNumberParticipant,
                'numberOfChatsParticipant' => $numberOfChatsParticipant,
                'totalHoursParticipant' => $totalHoursParticipant,
                'totalHoursParticipant_front' => erLhcoreClassChat::formatSeconds($totalHoursParticipant),
            );

            $statsRecord['mail_statistic_total'] = $statsRecord['mail_statistic_0'] + $statsRecord['mail_statistic_1'] + $statsRecord['mail_statistic_2'] + $statsRecord['mail_statistic_3'];

            // Allow extension append custom column
            erLhcoreClassChatEventDispatcher::getInstance()->dispatch('elasticsearch.getagentstatistic_display', array(
                'users_stats' => (isset($usersStats[$user->id]) ? $usersStats[$user->id] : null),
                'stats_record' => & $statsRecord
            ));

            $list[] = (object) $statsRecord;
        }
        
        return array(
            'status' => erLhcoreClassChatEventDispatcher::STOP_WORKFLOW,
            'list' => $list
        );
    }

    public static function statisticGettoptodaysoperators($params)
    {
        $elasticSearchHandler = erLhcoreClassElasticClient::getHandler();
        
        $sparams = array();
        $sparams['index'] = erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionElasticsearch')->settings['index_search'] . '-' . erLhcoreClassModelESChat::$elasticType;
        $sparams['body']['size'] = 0;
        $sparams['body']['from'] = 0;
        $sparams['ignore_unavailable'] = true;

        $useTimeFilter = ! isset($params['filter']['filtergte']['time']) && ! isset($params['filter']['filterlte']['time']);

        $paramsIndex = $params;
        if ($useTimeFilter == true) {
            $paramsIndex['filter']['filtergte']['time'] = $params['filter']['filtergt']['time'] = mktime(0, 0, 0, date('m'), date('d') - $params['days'], date('y'));
        }

        if (isset($_GET['abandoned_chat']) && $_GET['abandoned_chat'] == 1) {
            $params['filter']['filter']['abnd'] = 1;
        }

        if (isset($_GET['dropped_chat']) && $_GET['dropped_chat'] == 1) {
            $params['filter']['filter']['drpd'] = 1;
        }

        if (isset($_GET['transfer_happened']) && $_GET['transfer_happened'] == 1) {
            $sparams['body']['query']['bool']['must'][]['range']['transfer_uid']['gt'] = (int)0;
            $sparams['body']['query']['bool']['must'][]['range']['user_id']['gt'] = (int)0;
            $sparams['body']['query']['bool']['filter']['script']['script'] = "doc['user_id'].value != doc['transfer_uid'].value";
        }

        self::formatFilter($params['filter'], $sparams, array('subject_ids' => 'subject_id'));

        $indexSearch = self::getIndexByFilter($paramsIndex['filter'], erLhcoreClassModelESChat::$elasticType);

        if ($indexSearch != '') {
            $sparams['index'] = $indexSearch;
        }

        $sparams['body']['aggs']['group_by_user']['terms']['field'] = 'user_id';
        $sparams['body']['aggs']['group_by_user']['terms']['size'] = $params['limit'];
        $sparams['body']['aggs']['group_by_user']['aggs']['fb_status']['terms']['field'] = 'fbst';
        $sparams['body']['aggs']['group_by_user']['aggs']['fb_status']['terms']['size'] = 5;
        
        // Get grouped results
        $response = $elasticSearchHandler->search($sparams);
        
        $items = array();

        if (isset($response['aggregations']['group_by_user']['buckets'])){
            foreach ($response['aggregations']['group_by_user']['buckets'] as $item) {

                $statusMap = array();
                foreach ($item['fb_status']['buckets'] as $statusData) {
                    $statusMap[$statusData['key']] = $statusData['doc_count'];
                }

                $items[] = array(
                    'assigned_chats' => $item['doc_count'],
                    'user_id' => $item['key'],
                    'status' => $statusMap
                );
            }
        }
        
        // fill users
        $usersID = array();
        foreach ($items as $item) {
            $usersID[] = $item['user_id'];
        }
        
        if (! empty($usersID)) {
            $users = erLhcoreClassModelUser::getUserList(array(
                'limit' => $params['limit'],
                'filterin' => array(
                    'id' => $usersID
                )
            ));

            $indexSearch = self::getIndexByFilter($paramsIndex['filter'], erLhcoreClassModelESMsg::$elasticType);

            if ($indexSearch != '') {
                $sparams['index'] = $indexSearch;
            }

            $sparams['body']['query']['bool']['must'][]['terms']['user_id'] = $usersID;
            $totalMessagesByUser = $elasticSearchHandler->search($sparams);

            $usersStats = array();
            foreach ($totalMessagesByUser['aggregations']['group_by_user']['buckets'] as $item) {
                $usersStats[$item['key']] = $item['doc_count'];
            }
        }
        
        $usersReturn = array();
        
        foreach ($items as $row) {
            
            $user = null;
            if (isset($users[$row['user_id']])) {
                $user = $users[$row['user_id']];
            } else {
                $user = new erLhcoreClassModelUser();
                $user->id = $row['user_id'];
                $user->username = 'Not found user - ' . $row['user_id'];
            }
            
            $usersReturn[$row['user_id']] = $user;
            $usersReturn[$row['user_id']]->statistic_total_chats = $row['assigned_chats'];
            $usersReturn[$row['user_id']]->statistic_total_messages = isset($usersStats[$row['user_id']]) ? $usersStats[$row['user_id']] : 0;
            $usersReturn[$row['user_id']]->statistic_upvotes = isset($row['status'][1]) ? $row['status'][1] : 0;
            $usersReturn[$row['user_id']]->statistic_downvotes = isset($row['status'][2]) ? $row['status'][2] : 0;
        }
        
        return array(
            'status' => erLhcoreClassChatEventDispatcher::STOP_WORKFLOW,
            'list' => $usersReturn
        );
    }

    public static function nickGroupingDateNickDay($params) {
          return self::nickGroupingDateNick($params, 'day');
    }

    public static function nickGroupingDateNickWeekDay($params) {
          return self::nickGroupingDateNick($params, 'weekday');
    }

    public static function nickGroupingDateNick($params, $aggr = 'month')
    {
        $numberOfChats = array();

        $elasticSearchHandler = erLhcoreClassElasticClient::getHandler();

        $filterParams = $params['params_execution'];

        if (isset($_GET['abandoned_chat']) && $_GET['abandoned_chat'] == 1) {
            $params['filter']['filter']['abnd'] = 1;
        }

        if (isset($_GET['dropped_chat']) && $_GET['dropped_chat'] == 1) {
            $params['filter']['filter']['drpd'] = 1;
        }

        $sparams = array();
        $sparams['index'] = erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionElasticsearch')->settings['index_search'] . '-' . erLhcoreClassModelESChat::$elasticType;
        $sparams['ignore_unavailable'] = true;
        $sparams['body']['size'] = 0;
        $sparams['body']['from'] = 0;

        if (isset($_GET['transfer_happened']) && $_GET['transfer_happened'] == 1) {
            $sparams['body']['query']['bool']['must'][]['range']['transfer_uid']['gt'] = (int)0;
            $sparams['body']['query']['bool']['must'][]['range']['user_id']['gt'] = (int)0;
            $sparams['body']['query']['bool']['filter']['script']['script'] = "doc['user_id'].value != doc['transfer_uid'].value";
        }

        if ($aggr != 'weekday') {
            $sparams['body']['aggs']['chats_over_time']['date_histogram']['field'] = 'time';
            $sparams['body']['aggs']['chats_over_time']['date_histogram']['interval'] = $aggr;
            $sparams['body']['aggs']['chats_over_time']['date_histogram']['time_zone'] = self::getTimeZone();
        } else {
            $sparams['body']['aggs']['chats_over_time']['date_histogram']['script'] = "doc['time'].value.dayOfWeek;";
            $sparams['body']['aggs']['chats_over_time']['date_histogram']['interval'] = 1;
            $sparams['body']['aggs']['chats_over_time']['date_histogram']['extended_bounds']['min'] = 1;
            $sparams['body']['aggs']['chats_over_time']['date_histogram']['extended_bounds']['max'] = 7;
            $sparams['body']['aggs']['chats_over_time']['date_histogram']['time_zone'] = self::getTimeZone();
        }

        $validGroupFields = array(
            'nick' => 'nick_keyword',
            'uagent' => 'uagent',
            'device_type' => 'device_type',
            'department' => 'dep_id',
            'user_id' => 'user_id',
            'transfer_uid' => 'transfer_uid',
        );

        erLhcoreClassChatEventDispatcher::getInstance()->dispatch('statistic.validgroupfields', array('type' => 'elastic', 'fields' => & $validGroupFields));

        if ($filterParams['group_field'] == 'device_type') {
            return;
        }

        $attr = 'nick_keyword';
        if (isset($filterParams['group_field']) && key_exists($filterParams['group_field'], $validGroupFields)) {
            $attr = $validGroupFields[$filterParams['group_field']];
        }

       $sparams['body']['aggs']['chats_over_time']['aggs']['status_aggr']['terms']['field'] = $attr;
       $sparams['body']['aggs']['chats_over_time']['aggs']['status_aggr']['terms']['size'] = (isset($params['params_execution']['group_limit']) && is_numeric($params['params_execution']['group_limit'])) ? (int)$params['params_execution']['group_limit'] : 10;

        $paramsOrig = $paramsOrigIndex = $params;
        if ($aggr == 'month') {
            if (!isset($paramsOrig['filter']['filtergte']['time'])) {
                $paramsOrigIndex['filter']['filtergte']['time'] = $paramsOrig['filter']['filtergt']['time'] = time() - (24 * 366 * 3600); // Limit results to one year
            }
        } else {
            if (! isset($paramsOrig['filter']['filtergte']['time']) && ! isset($paramsOrig['filter']['filterlte']['time'])) {
                $paramsOrigIndex['filter']['filtergte']['time'] = $paramsOrig['filter']['filtergt']['time'] = mktime(0, 0, 0, date('m'), date('d') - 31, date('y'));
            }
        }

        $indexSearch = self::getIndexByFilter($paramsOrigIndex['filter'], erLhcoreClassModelESChat::$elasticType);

        if ($indexSearch != '') {
            $sparams['index'] = $indexSearch;
        }

        self::formatFilter($paramsOrig['filter'], $sparams, array('subject_ids' => 'subject_id'));

        erLhcoreClassChatEventDispatcher::getInstance()->dispatch('statistic.nickbroupingdatenick_filter', array('sparams' => & $sparams));

        $response = $elasticSearchHandler->search($sparams);

        foreach ($response['aggregations']['chats_over_time']['buckets'] as $bucket) {

            if ($bucket['key'] > 10) {
                $keyDateUnix = $bucket['key'] / 1000;
            } else {
                $keyDateUnix = $bucket['key'];
            }

            $returnArray = array();

            foreach ($bucket['status_aggr']['buckets'] as $bucketStatus) {

                $returnArray['color'][] = json_encode(erLhcoreClassChatStatistic::colorFromString($bucketStatus['key']));

                if ($attr == 'device_type') {
                    $returnArray['nick'][] = json_encode($bucketStatus['key'] == 0 ? 'PC' : ($bucketStatus['key'] == 1 ? 'Mobile' : 'Table'));
                } elseif ($attr == 'user_id' || $attr == 'transfer_uid') {
                    $returnArray['nick'][] = json_encode($bucketStatus['key'] > 0 && ($userAttr = erLhcoreClassModelUser::fetch($bucketStatus['key'])) && $userAttr instanceof erLhcoreClassModelUser ? $userAttr->name_official : $bucketStatus['key']);
                } elseif ($attr == 'dep_id') {
                    $returnArray['nick'][] = json_encode((string)erLhcoreClassModelDepartament::fetch($bucketStatus['key']));
                } else {
                    $returnArray['nick'][] = json_encode($bucketStatus['key']);
                }

                $returnArray['data'][] = $bucketStatus['doc_count'];
            }

            $numberOfChats[$keyDateUnix] = $returnArray;
        }

        if ($aggr == 'weekday') {
            $sundayData = $numberOfChats[7];
            unset($numberOfChats[7]);
            $numberOfChats[0] = $sundayData;
        }

        $returnReversed = array();

        $limitDays = (isset($params['params_execution']['group_limit']) && is_numeric($params['params_execution']['group_limit'])) ? (int)$params['params_execution']['group_limit'] : 10;

        foreach ($numberOfChats as $dateIndex => $returnData) {
            for ($i = 0; $i < $limitDays; $i++) {
                $returnReversed[$i]['data'][] = isset($returnData['data'][$i]) ? $returnData['data'][$i] : 0;
                $returnReversed[$i]['color'][] = isset($returnData['color'][$i]) ? $returnData['color'][$i] : '""';
                $returnReversed[$i]['nick'][] = isset($returnData['nick'][$i]) ? $returnData['nick'][$i] : '""';
            }
        }

        return array(
            'status' => erLhcoreClassChatEventDispatcher::STOP_WORKFLOW,
            'list' => array('labels' => $numberOfChats, 'data' => $returnReversed)
        );
    }

    public static function nickGroupingDateDay($params)
    {
        return self::nickGroupingDate($params, 'day');
    }

    public static function nickGroupingDateWeekDay($params)
    {
        return self::nickGroupingDate($params, 'weekday');
    }

    public static function nickGroupingDate($params, $aggr = 'month')
    {
        $elasticSearchHandler = erLhcoreClassElasticClient::getHandler();

        $filterParams = $params['params_execution'];

        $sparams = array();
        $sparams['index'] = erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionElasticsearch')->settings['index_search'] . '-' . erLhcoreClassModelESChat::$elasticType;
        $sparams['ignore_unavailable'] = true;
        $sparams['body']['size'] = 0;
        $sparams['body']['from'] = 0;

        if ($aggr != 'weekday') {
            $sparams['body']['aggs']['chats_over_time']['date_histogram']['field'] = 'time';
            $sparams['body']['aggs']['chats_over_time']['date_histogram']['interval'] = $aggr;
            $sparams['body']['aggs']['chats_over_time']['date_histogram']['time_zone'] = self::getTimeZone();
        } else {
            $sparams['body']['aggs']['chats_over_time']['date_histogram']['script'] = "doc['time'].value.dayOfWeek;";
            $sparams['body']['aggs']['chats_over_time']['date_histogram']['interval'] = 1;
            $sparams['body']['aggs']['chats_over_time']['date_histogram']['extended_bounds']['min'] = 1;
            $sparams['body']['aggs']['chats_over_time']['date_histogram']['extended_bounds']['max'] = 7;
            $sparams['body']['aggs']['chats_over_time']['date_histogram']['time_zone'] = self::getTimeZone();
        }

        if (isset($_GET['abandoned_chat']) && $_GET['abandoned_chat'] == 1) {
            $params['filter']['filter']['abnd'] = 1;
        }

        if (isset($_GET['dropped_chat']) && $_GET['dropped_chat'] == 1) {
            $params['filter']['filter']['drpd'] = 1;
        }

        if (isset($_GET['transfer_happened']) && $_GET['transfer_happened'] == 1) {
            $sparams['body']['query']['bool']['must'][]['range']['transfer_uid']['gt'] = (int)0;
            $sparams['body']['query']['bool']['must'][]['range']['user_id']['gt'] = (int)0;
            $sparams['body']['query']['bool']['filter']['script']['script'] = "doc['user_id'].value != doc['transfer_uid'].value";
        }

        $validGroupFields = array(
            'nick' => 'nick_keyword',
            'uagent' => 'uagent',
            'device_type' => 'device_type',
        );

        erLhcoreClassChatEventDispatcher::getInstance()->dispatch('statistic.validgroupfields', array('type' => 'elastic', 'fields' => & $validGroupFields));

        if ($filterParams['group_field'] == 'device_type') {
            return;
        }

        $attr = 'nick_keyword';
        if (isset($filterParams['group_field']) && key_exists($filterParams['group_field'], $validGroupFields)) {
            $attr = $validGroupFields[$filterParams['group_field']];
        }

        $sparams['body']['aggs']['chats_over_time']['aggs']['status_aggr']['cardinality']['field'] = $attr;

        $sparams['body']['aggs']['chats_over_time']['date_histogram']['time_zone'] = self::getTimeZone();

        $paramsOrig = $paramsOrigIndex = $params;
        if ($aggr == 'month') {
            if (!isset($paramsOrig['filter']['filtergte']['time'])) {
                $paramsOrigIndex['filter']['filtergte']['time'] = $paramsOrig['filter']['filtergt']['time'] = time() - (24 * 366 * 3600); // Limit results to one year
            }
        } else {
            if (! isset($paramsOrig['filter']['filtergte']['time']) && ! isset($paramsOrig['filter']['filterlte']['time'])) {
                $paramsOrigIndex['filter']['filtergte']['time'] = $paramsOrig['filter']['filtergt']['time'] = mktime(0, 0, 0, date('m'), date('d') - 31, date('y'));
            }
        }

        $indexSearch = self::getIndexByFilter($paramsOrigIndex['filter'], erLhcoreClassModelESChat::$elasticType);

        if ($indexSearch != '') {
            $sparams['index'] = $indexSearch;
        }

        self::formatFilter($paramsOrig['filter'], $sparams, array('subject_ids' => 'subject_id'));

        erLhcoreClassChatEventDispatcher::getInstance()->dispatch('statistic.nickgroupingdate_filter', array('sparams' => & $sparams));

        $response = $elasticSearchHandler->search($sparams);

        $numberOfChats = array();

        foreach ($response['aggregations']['chats_over_time']['buckets'] as $bucket) {

            if ($bucket['key'] > 10) {
                $keyDateUnix = $bucket['key'] / 1000;
            } else {
                $keyDateUnix = $bucket['key'];
            }

            $numberOfChats[$keyDateUnix] = array ();
            $numberOfChats[$keyDateUnix]['unique'] = (int)$bucket['status_aggr']['value'];
        }

        if ($aggr == 'weekday') {
            $sundayData = $numberOfChats[7];
            unset($numberOfChats[7]);
            $numberOfChats[0] = $sundayData;
        }

        return array(
            'status' => erLhcoreClassChatEventDispatcher::STOP_WORKFLOW,
            'list' => $numberOfChats
        );
    }

    public static function formatFilter($params, & $sparams, $customFields = array())
    {
        $returnFilter = array();
        
        foreach ($params as $type => $params) {
            foreach ($params as $field => $value) {
                
                $field = str_replace('lh_chat.', '', $field);
                $field = str_replace('lhc_mailconv_msg.', '', $field);
                $field = str_replace('lh_chat_participant.', '', $field);

                if ($field == 'time' || $field == 'itime') {
                    $value = $value * 1000;
                }
                
                if ($type == 'filter') {
                    $sparams['body']['query']['bool']['must'][]['term'][$field] = $value;
                } elseif ($type == 'filterlte') {
                    $sparams['body']['query']['bool']['must'][]['range'][$field]['lte'] = $value;
                } elseif ($type == 'filterlt') {
                    $sparams['body']['query']['bool']['must'][]['range'][$field]['lt'] = $value;
                } elseif ($type == 'filtergte') {
                    $sparams['body']['query']['bool']['must'][]['range'][$field]['gte'] = $value;
                } elseif ($type == 'filtergt') {
                    $sparams['body']['query']['bool']['must'][]['range'][$field]['gt'] = $value;
                } elseif ($type == 'filterin') {
                    $sparams['body']['query']['bool']['must'][]['terms'][$field] = array_values($value);
                } elseif ($type == 'filterm') {
                    $sparams['body']['query']['bool']['must'][]['term'][$field] = $value;
                } elseif ($type == 'filterinm') {
                    $sparams['body']['query']['bool']['must'][]['terms'][$field] = $value;
                } elseif ($type == 'filterin_elastic' && isset($customFields[$field])) {
                    $sparams['body']['query']['bool']['must'][]['terms'][$customFields[$field]] = array_values($value);
                } elseif ($type == 'filterlike' && $field == 'city') {
                    $sparams['body']['query']['bool']['must'][]['term']['region'] = $value;
                }
            }
        }
        
        return $returnFilter;
    }

    public static function statisticsubjectsStatistic($params) {
        
        $elasticSearchHandler = erLhcoreClassElasticClient::getHandler();

        $sparams = array();
        $sparams['index'] = erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionElasticsearch')->settings['index_search'] . '-' . erLhcoreClassModelESChat::$elasticType;
        $sparams['ignore_unavailable'] = true;

        if (isset($_GET['abandoned_chat']) && $_GET['abandoned_chat'] == 1) {
            $params['filter']['filter']['abnd'] = 1;
        }

        if (isset($_GET['dropped_chat']) && $_GET['dropped_chat'] == 1) {
            $params['filter']['filter']['drpd'] = 1;
        }

        if (isset($_GET['transfer_happened']) && $_GET['transfer_happened'] == 1) {
            $sparams['body']['query']['bool']['must'][]['range']['transfer_uid']['gt'] = (int)0;
            $sparams['body']['query']['bool']['must'][]['range']['user_id']['gt'] = (int)0;
            $sparams['body']['query']['bool']['filter']['script']['script'] = "doc['user_id'].value != doc['transfer_uid'].value";
        }

        self::formatFilter($params['filter'], $sparams, array('subject_ids' => 'subject_id'));

        if (! isset($params['filter']['filtergte']['time']) && ! isset($params['filter']['filterlte']['time'])) {
            $ts = mktime(0, 0, 0, date('m'), date('d') - $params['days'], date('y'));
            $sparams['body']['query']['bool']['must'][]['range']['time']['gt'] = $ts * 1000;
            $params['filter']['filtergte']['time'] = $ts;
        }

        $indexSearch = self::getIndexByFilter($params['filter'], erLhcoreClassModelESChat::$elasticType);

        if ($indexSearch != '') {
            $sparams['index'] = $indexSearch;
        }

        $field = 'subject_id';

        $statusWorkflow = erLhcoreClassChatEventDispatcher::getInstance()->dispatch('elasticsearch.getsubjectsstatistic_field', array('field' => $field));

        if ($statusWorkflow !== false) {
            $field = $statusWorkflow['field'];
        }
        
        $sparams['body']['size'] = 0;
        $sparams['body']['from'] = 0;
        $sparams['body']['aggs']['group_by_country_count']['terms']['field'] = $field;
        $sparams['body']['aggs']['group_by_country_count']['terms']['size'] = 40;

        $response = $elasticSearchHandler->search($sparams);

        $statsAggr = array();

        foreach ($response['aggregations']['group_by_country_count']['buckets'] as $item) {
            $statsAggr[] = array(
                'number_of_chats' => $item['doc_count'],
                'subject_id' => (trim($item['key']) == '' ? '-' : $item['key'])
            );
        }

        return array(
            'status' => erLhcoreClassChatEventDispatcher::STOP_WORKFLOW,
            'list' => $statsAggr
        );
    }
    
    public static function getPreviousChatsByChat($chat) {

    }

    public static function mailMessagesperinterval($params) {
        
        if ($params['lhc_caller']['function'] == 'messagesPerInterval' && (in_array('mavgwaittime',$params['params_execution']['chart_type']) || in_array('mmsgperinterval',$params['params_execution']['chart_type']))) {
            $params['params_execution']['chart_type'] = ['mavgwaittime','mmsgperinterval'];
        } elseif ($params['lhc_caller']['function'] == 'messagesPerUser'  &&  in_array('mmsgperuser',$params['params_execution']['chart_type'])) {
            $params['params_execution']['chart_type'] = ['mmsgperuser'];
        } elseif ($params['lhc_caller']['function'] == 'messagesPerDep' && in_array('mmsgperdep',$params['params_execution']['chart_type'])) {
            $params['params_execution']['chart_type'] = ['mmsgperdep'];
        } elseif ($params['lhc_caller']['function'] == 'messagesPerHour' && in_array('msgperhour',$params['params_execution']['chart_type'])) {
            $params['params_execution']['chart_type'] = ['msgperhour'];
        } elseif ($params['lhc_caller']['function'] == 'avgInteractionPerDep' && in_array('mmintperdep',$params['params_execution']['chart_type'])) {
            $params['params_execution']['chart_type'] = ['mmintperdep'];
        } elseif ($params['lhc_caller']['function'] == 'avgInteractionPerUser' && in_array('mmintperuser',$params['params_execution']['chart_type'])) {
            $params['params_execution']['chart_type'] = ['mmintperuser'];
        } elseif ($params['lhc_caller']['function'] == 'attrByPerInterval' && in_array('mattrgroup',$params['params_execution']['chart_type'])) {
            $params['params_execution']['chart_type'] = ['mattrgroup'];
        }

        return self::mailMessagesperintervalprocess($params);
    }

    public static function mailMessagesperintervalprocess($params) {

        $numberOfChats = array();

        $elasticSearchHandler = erLhcoreClassElasticClient::getHandler();

        $sparams = array();
        $sparams['index'] = erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionElasticsearch')->settings['index_search'] . '-' . erLhcoreClassModelESMail::$elasticType;
        $sparams['ignore_unavailable'] = true;
        $sparams['body']['size'] = 0;
        $sparams['body']['from'] = 0;

        $aggr = [1 => 'day', 0 => 'month'];

        $sparams['body']['aggs']['chats_over_time']['date_histogram']['field'] = 'time';
        $sparams['body']['aggs']['chats_over_time']['date_histogram']['interval'] = $aggr[$params['params_execution']['group_by']];
        $sparams['body']['aggs']['chats_over_time']['date_histogram']['time_zone'] = self::getTimeZone();

        if (is_array($params['params_execution']['chart_type']) && (in_array('mmsgperinterval',$params['params_execution']['chart_type'])) ) {
            $sparams['body']['aggs']['chats_over_time']['aggs']['response_type']['terms']['field'] = 'response_type';
        }

        if (in_array('mavgwaittime',$params['params_execution']['chart_type'])) {
            $sparams['body']['aggs']['chats_over_time']['aggs']['avg_wait_time']['filter']['range']['wait_time']['lt'] = 600;
            $sparams['body']['aggs']['chats_over_time']['aggs']['avg_wait_time']['filter']['range']['wait_time']['gt'] = 0;
            $sparams['body']['aggs']['chats_over_time']['aggs']['avg_wait_time']['aggs']['wait_time_avg']['avg']['field'] = 'wait_time';
        }

        if (is_array($params['params_execution']['chart_type']) && in_array('mmsgperuser', $params['params_execution']['chart_type'])) {
            $sparams['body']['aggs']['chat_user_aggr']['terms']['size'] = 50;
            $sparams['body']['aggs']['chat_user_aggr']['terms']['field'] = 'user_id';
        }

        if (is_array($params['params_execution']['chart_type']) && in_array('mmsgperdep', $params['params_execution']['chart_type'])) {
            $sparams['body']['aggs']['chat_dep_aggr']['terms']['size'] = 50;
            $sparams['body']['aggs']['chat_dep_aggr']['terms']['field'] = 'dep_id';
        }
        
        if (is_array($params['params_execution']['chart_type']) && in_array('msgperhour', $params['params_execution']['chart_type'])) {
            $sparams['body']['aggs']['chat_by_hour']['terms']['field'] = 'hour';
            $sparams['body']['aggs']['chat_by_hour']['terms']['size'] = 48;
        }

        if (is_array($params['params_execution']['chart_type']) && in_array('mmintperdep', $params['params_execution']['chart_type'])) {
            $params['filter']['filtergte']['interaction_time'] = 1;
            $params['filter']['filterlte']['interaction_time'] = 600;
            $sparams['body']['aggs']['chat_dep_aggr_int']['terms']['field'] = 'dep_id';
            $sparams['body']['aggs']['chat_dep_aggr_int']['terms']['size'] = 50;
            $sparams['body']['aggs']['chat_dep_aggr_int']['aggs']['interaction_time']['avg']['field'] = 'interaction_time';
        }

        if (is_array($params['params_execution']['chart_type']) && in_array('mmintperuser', $params['params_execution']['chart_type'])) {
            $params['filter']['filtergte']['interaction_time'] = 1;
            $params['filter']['filterlte']['interaction_time'] = 600;
            $sparams['body']['aggs']['chat_user_aggr_int']['terms']['field'] = 'user_id';
            $sparams['body']['aggs']['chat_user_aggr_int']['terms']['size'] = 50;
            $sparams['body']['aggs']['chat_user_aggr_int']['aggs']['interaction_time']['avg']['field'] = 'interaction_time';
        }

        if (is_array($params['params_execution']['chart_type']) && in_array('mattrgroup', $params['params_execution']['chart_type'])) {

            $validGroupFields = array(
                'user_id' => 'user_id',
                'dep_id' => 'dep_id',
                'mailbox_id' => 'mailbox_id',
                'response_type' => 'response_type',
            );
            if (isset($params['params_execution']['group_field']) && key_exists($params['params_execution']['group_field'], $validGroupFields)) {
                $groupField = $validGroupFields[$params['params_execution']['group_field']];
                $attr = $params['params_execution']['group_field'];
            } else {
                return [];
            }
            $sparams['body']['aggs']['chats_over_time']['aggs']['chat_attr_group_multi']['terms']['field'] = $attr;
            $sparams['body']['aggs']['chats_over_time']['aggs']['chat_attr_group_multi']['terms']['size'] = (isset($params['params_execution']['group_limit']) && is_numeric($params['params_execution']['group_limit'])) ? (int)$params['params_execution']['group_limit'] : 10;
        }


        if (isset($params['filter']['filtergte']['udate'])) {
            $params['filter']['filtergte']['time'] = $params['filter']['filtergte']['udate'];
            unset($params['filter']['filtergte']['udate']);
        }

        if (isset($params['filter']['filterlte']['udate'])) {
            $params['filter']['filterlte']['time'] = $params['filter']['filterlte']['udate'];
            unset($params['filter']['filterlte']['udate']);
        }

        $paramsOrig = $paramsOrigIndex = $params;
        if ($aggr[$params['params_execution']['group_by']] == 'month') {
            if (!isset($paramsOrig['filter']['filtergte']['time'])) {
                $paramsOrigIndex['filter']['filtergte']['time'] = $paramsOrig['filter']['filtergte']['time'] = time() - (24 * 366 * 3600); // Limit results to one year
            }
        } else {
            if (! isset($paramsOrig['filter']['filtergte']['time']) && ! isset($paramsOrig['filter']['filterlte']['time'])) {
                $paramsOrigIndex['filter']['filtergte']['time'] = $paramsOrig['filter']['filtergte']['time'] = mktime(0, 0, 0, date('m'), date('d') - 31, date('y'));
            }
        }

        $indexSearch = self::getIndexByFilter($paramsOrigIndex['filter'], erLhcoreClassModelESMail::$elasticType);

        if ($indexSearch != '') {
            $sparams['index'] = $indexSearch;
        }

        self::formatFilter($paramsOrig['filter'], $sparams, array('subject_ids' => 'subject_id'));


        $response = $elasticSearchHandler->search($sparams);

        $valuesResponse = [
            erLhcoreClassModelMailconvMessage::RESPONSE_NORMAL => 'normal',
            erLhcoreClassModelMailconvMessage::RESPONSE_NOT_REQUIRED => 'notrequired',
            erLhcoreClassModelMailconvMessage::RESPONSE_INTERNAL => 'send',
            erLhcoreClassModelMailconvMessage::RESPONSE_UNRESPONDED => 'unresponded',
        ];

        if (is_array($params['params_execution']['chart_type']) && in_array('mattrgroup', $params['params_execution']['chart_type'])) {

            $responseTypes = array(
                erLhcoreClassModelMailconvMessage::RESPONSE_UNRESPONDED => erTranslationClassLhTranslation::getInstance()->getTranslation('module/mailconv','Unresponded'),
                erLhcoreClassModelMailconvMessage::RESPONSE_NOT_REQUIRED => erTranslationClassLhTranslation::getInstance()->getTranslation('module/mailconv','No reply required'),
                erLhcoreClassModelMailconvMessage::RESPONSE_INTERNAL => erTranslationClassLhTranslation::getInstance()->getTranslation('module/mailconv','Send messages'),
                erLhcoreClassModelMailconvMessage::RESPONSE_NORMAL => erTranslationClassLhTranslation::getInstance()->getTranslation('module/mailconv','Responded by e-mail'),
            );

            $numberOfChats = [];

            foreach ($response['aggregations']['chats_over_time']['buckets'] as $bucketGroup) {

                if ($bucketGroup['key'] > 10) {
                    $keyDateUnix = $bucketGroup['key'] / 1000;
                } else {
                    $keyDateUnix = $bucketGroup['key'];
                }

                $returnArray = array();

                foreach ($bucketGroup['chat_attr_group_multi']['buckets'] as $bucket) {
                    $returnArray['color'][] = json_encode(erLhcoreClassChatStatistic::colorFromString($bucket['key']));
                    if ($attr == 'user_id') {
                        $returnArray['nick'][] = json_encode($bucket['key'] > 0 && ($itemStat = erLhcoreClassModelUser::fetch($bucket['key'],true)) ? $itemStat->name_official : erTranslationClassLhTranslation::getInstance()->getTranslation('module/mailconv','Not assigned'));
                    } else if ($attr == 'dep_id') {
                        $returnArray['nick'][] = json_encode($bucket['key'] > 0 && ($itemStat = erLhcoreClassModelDepartament::fetch($bucket['key'])) ? (string)$itemStat : erTranslationClassLhTranslation::getInstance()->getTranslation('module/mailconv','Not assigned'));
                    } else if ($attr == 'mailbox_id') {
                        $returnArray['nick'][] = json_encode($bucket['key'] > 0 && ($itemStat = erLhcoreClassModelMailconvMailbox::fetch($bucket['key'])) ? (string)$itemStat : erTranslationClassLhTranslation::getInstance()->getTranslation('module/mailconv','Not assigned'));
                    } else if ($attr == 'response_type') {
                        $returnArray['nick'][] = json_encode($responseTypes[$bucket['key']]);
                    } else {
                        $returnArray['nick'][] = json_encode($bucket['key']);
                    }

                    $returnArray['data'][] = $bucket['doc_count'];
                }

                $numberOfChats[$keyDateUnix] = $returnArray;
            }

            $returnReversed = array();

            $limitReverse = (isset($params['params_execution']['group_limit']) && is_numeric($params['params_execution']['group_limit'])) ? (int)$params['params_execution']['group_limit'] : 10;

            foreach ($numberOfChats as $dateIndex => $returnData) {
                for ($i = 0; $i < $limitReverse; $i++) {
                    $returnReversed[$i]['data'][] = isset($returnData['data'][$i]) ? $returnData['data'][$i] : 0;
                    $returnReversed[$i]['color'][] = isset($returnData['color'][$i]) ? $returnData['color'][$i] : '""';
                    $returnReversed[$i]['nick'][] = isset($returnData['nick'][$i]) ? $returnData['nick'][$i] : '""';
                }
            }

            return array(
                'status' => erLhcoreClassChatEventDispatcher::STOP_WORKFLOW,
                'list' => array('labels' => $numberOfChats, 'data' => $returnReversed)
            );
        }

        if (in_array('mmintperuser', $params['params_execution']['chart_type'])) {
            $numberOfChats = [];
            foreach ($response['aggregations']['chat_user_aggr_int']['buckets'] as $bucket) {
                $numberOfChats[] = [
                    'user_id' => $bucket['key'],
                    'interaction_time' => $bucket['interaction_time']['value']
                ];
            }
            return array(
                'status' => erLhcoreClassChatEventDispatcher::STOP_WORKFLOW,
                'list' => $numberOfChats
            );
        }

        if (in_array('msgperhour', $params['params_execution']['chart_type'])) {

            $numberOfChats['total'] = array_fill(1, 23, 0);
            $numberOfChats['byday'] = array_fill(1, 23, 0);

            $dateTime = new DateTime("now");
            $utcAdjust = $dateTime->getOffset() / 60 / 60; // Hours are stored in UTC format. We need to adjust filters

            $diffDays = ((isset($paramsOrig['filter']['filterlte']['time']) ? $paramsOrig['filter']['filterlte']['time'] : time())-$paramsOrig['filter']['filtergte']['time'])/(24*3600);

            foreach ($response['aggregations']['chat_by_hour']['buckets'] as $item) {
                $hourAdjusted = $item['key'] + $utcAdjust;

                if ($hourAdjusted < 0){
                    $hourAdjusted = 24 + $hourAdjusted;
                }

                if ($hourAdjusted > 23) {
                    $hourAdjusted = $hourAdjusted - 24;
                }

                $numberOfChats['total'][$hourAdjusted] = $item['doc_count'];
                $numberOfChats['byday'][$hourAdjusted] = $item['doc_count']/$diffDays;
            }

            ksort($numberOfChats, SORT_NUMERIC);

            return array(
                'status' => erLhcoreClassChatEventDispatcher::STOP_WORKFLOW,
                'list' => $numberOfChats
            );
        }

        if (in_array('mmintperdep', $params['params_execution']['chart_type'])) {
            $numberOfChats = [];
            foreach ($response['aggregations']['chat_dep_aggr_int']['buckets'] as $bucket) {
                $numberOfChats[] = [
                    'dep_id' => $bucket['key'],
                    'interaction_time' => $bucket['interaction_time']['value']
                ];
            }
            return array(
                'status' => erLhcoreClassChatEventDispatcher::STOP_WORKFLOW,
                'list' => $numberOfChats
            );
        }


        if (in_array('mmsgperuser', $params['params_execution']['chart_type'])) {
            $numberOfChats = [];
            foreach ($response['aggregations']['chat_user_aggr']['buckets'] as $bucket) {
                $numberOfChats[] = [
                    'user_id' => $bucket['key'],
                    'total_records' => $bucket['doc_count']
                ];
            }
            return array(
                'status' => erLhcoreClassChatEventDispatcher::STOP_WORKFLOW,
                'list' => $numberOfChats
            );
        }

        if (in_array('mmsgperdep', $params['params_execution']['chart_type'])) {
            $numberOfChats = [];
            foreach ($response['aggregations']['chat_dep_aggr']['buckets'] as $bucket) {
                $numberOfChats[] = [
                    'dep_id' => $bucket['key'],
                    'total_records' => $bucket['doc_count']
                ];
            }
            return array(
                'status' => erLhcoreClassChatEventDispatcher::STOP_WORKFLOW,
                'list' => $numberOfChats
            );
        }

        foreach ($response['aggregations']['chats_over_time']['buckets'] as $bucket) {

            if ($bucket['key'] > 10) {
                $keyDateUnix = $bucket['key'] / 1000;
            } else {
                $keyDateUnix = $bucket['key'];
            }

            if (in_array('mavgwaittime', $params['params_execution']['chart_type'])) {
                $numberOfChats[$keyDateUnix]['avg_wait_time'] = $bucket['avg_wait_time']['wait_time_avg']['value'];
            }

            if (in_array('mmsgperinterval', $params['params_execution']['chart_type'])) {
                $numberOfChats[$keyDateUnix]['unresponded'] =
                $numberOfChats[$keyDateUnix]['send'] =
                $numberOfChats[$keyDateUnix]['notrequired'] =
                $numberOfChats[$keyDateUnix]['normal'] = 0;
                foreach ($bucket['response_type']['buckets'] as $bucketResponse) {
                    $numberOfChats[$keyDateUnix][$valuesResponse[$bucketResponse['key']]] = $bucketResponse['doc_count'];
                }
            }
        }

        return array(
            'status' => erLhcoreClassChatEventDispatcher::STOP_WORKFLOW,
            'list' => $numberOfChats
        );
    }

}