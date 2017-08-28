<?php

class erLhcoreClassElasticSearchStatistic
{

    public static function statisticGettopchatsbycountry($params)
    {
        $elasticSearchHandler = erLhcoreClassElasticClient::getHandler();
        
        $sparams = array();
        $sparams['index'] = erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionElasticsearch')->settings['index'];
        $sparams['type'] = erLhcoreClassModelESChat::$elasticType;
        
        self::formatFilter($params['filter'], $sparams);
        
        if (! isset($params['filter']['filtergte']['time']) && ! isset($params['filter']['filterlte']['time'])) {
            $sparams['body']['query']['bool']['must'][]['range']['time']['gt'] = mktime(0, 0, 0, date('m'), date('d') - $params['days'], date('y')) * 1000;
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

            $groupByData = array(
                'interval' => 60000,
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
            $sparams['index'] = erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionElasticsearch')->settings['index'];
            $sparams['type'] = erLhcoreClassModelESPendingChat::$elasticType;
          
            if (! isset($filterParams['filter']['filtergte']['itime']) && ! isset($filterParams['filter']['filterlte']['itime'])) {
                $filterParams['filter']['filtergte']['itime'] = time()-(24*3600);
            }

            erLhcoreClassChatStatistic::formatUserFilter($filterParams);

            self::formatFilter($filterParams['filter'], $sparams);

            $sparams['body']['size'] = 0;
            $sparams['body']['from'] = 0;
            $sparams['body']['aggs']['chats_over_time']['date_histogram']['field'] = 'itime';
            $sparams['body']['aggs']['chats_over_time']['date_histogram']['interval'] = $groupByData['interval'];
            
            $dateTime = new DateTime("now");
            $sparams['body']['aggs']['chats_over_time']['date_histogram']['time_zone'] = $dateTime->getOffset() / 60 / 60;
            
            $sparams['body']['aggs']['chats_over_time']['aggs']['chat_status']['terms']['field'] = 'status';
            $response = $elasticSearchHandler->search($sparams);
            
            $numberOfChats = array();
           
            $keyStatus = array(
                erLhcoreClassModelChat::STATUS_ACTIVE_CHAT => 'active',
                erLhcoreClassModelChat::STATUS_PENDING_CHAT => 'pending'
            );

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

            $sparams = array();
            $sparams['index'] = erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionElasticsearch')->settings['index'];
            $sparams['type'] = erLhcoreClassModelESOnlineOperator::$elasticType;

            if (isset($filterParams['filter']['filterin']['lh_chat.dep_id'])) {
                $filterParams['filter']['filterinm']['dep_ids'] = $filterParams['filter']['filterin']['lh_chat.dep_id'];
                unset($filterParams['filter']['filterin']['lh_chat.dep_id']);
            } elseif (isset($filterParams['filter']['filter']['dep_id'])) {
                $filterParams['filter']['filterm']['dep_ids'] = $filterParams['filter']['filter']['dep_id'];
                unset($filterParams['filter']['filter']['dep_id']);
            };

            self::formatFilter($filterParams['filter'], $sparams);

            $sparams['body']['size'] = 0;
            $sparams['body']['from'] = 0;
            $sparams['body']['aggs']['chats_over_time']['date_histogram']['field'] = 'itime';
            $sparams['body']['aggs']['chats_over_time']['date_histogram']['interval'] = $groupByData['interval'];
            
            $dateTime = new DateTime("now");
            $sparams['body']['aggs']['chats_over_time']['date_histogram']['time_zone'] = $dateTime->getOffset() / 60 / 60;
            $response = $elasticSearchHandler->search($sparams);

            foreach ($response['aggregations']['chats_over_time']['buckets'] as $bucket) {
                $indexBucket = $bucket['key']/1000;
                $numberOfChats[$indexBucket]['op_count'] = $bucket['doc_count'] / $groupByData['divide'];
                
                foreach ($keyStatus as $mustHave) {
                    if (! isset($numberOfChats[$indexBucket][$mustHave])) {
                        $numberOfChats[$indexBucket][$mustHave] = 0;
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

    public static function statisticNumberofchatsdialogsbydepartment($params)
    {
        $elasticSearchHandler = erLhcoreClassElasticClient::getHandler();

        $sparams = array();
        $sparams['index'] = erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionElasticsearch')->settings['index'];
        $sparams['type'] = erLhcoreClassModelESChat::$elasticType;

        self::formatFilter($params['filter'], $sparams);

        if (! isset($params['filter']['filtergte']['time']) && ! isset($params['filter']['filterlte']['time'])) {
            $sparams['body']['query']['bool']['must'][]['range']['time']['gt'] = mktime(0, 0, 0, date('m'), date('d') - $params['days'], date('y')) * 1000;
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
        $sparams['index'] = erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionElasticsearch')->settings['index'];
        $sparams['type'] = erLhcoreClassModelESChat::$elasticType;
        
        self::formatFilter($params['filter'], $sparams);
        
        if (! isset($params['filter']['filtergte']['time']) && ! isset($params['filter']['filterlte']['time'])) {
            $sparams['body']['query']['bool']['must'][]['range']['time']['gt'] = mktime(0, 0, 0, date('m'), date('d') - $params['days'], date('y')) * 1000;
        }
        
        $sparams['body']['size'] = 0;
        $sparams['body']['from'] = 0;
        $sparams['body']['aggs']['group_by_country_count']['terms']['field'] = 'user_id';
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

    public static function statisticAvgwaittimeuser($params)
    {
        $elasticSearchHandler = erLhcoreClassElasticClient::getHandler();
        
        $sparams = array();
        $sparams['index'] = erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionElasticsearch')->settings['index'];
        $sparams['type'] = erLhcoreClassModelESChat::$elasticType;
        
        $params['filter']['filterlt']['wait_time'] = 600;
        
        self::formatFilter($params['filter'], $sparams);
        
        if (! isset($params['filter']['filtergte']['time']) && ! isset($params['filter']['filterlte']['time'])) {
            $sparams['body']['query']['bool']['must'][]['range']['time']['gt'] = mktime(0, 0, 0, date('m'), date('d') - $params['days'], date('y')) * 1000;
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
        $sparams['index'] = erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionElasticsearch')->settings['index'];
        $sparams['type'] = erLhcoreClassModelESChat::$elasticType;
        
        $params['filter']['filtergt']['chat_duration'] = 0;
        $params['filter']['filterlt']['chat_duration'] = 60*60;
        $params['filter']['filtergt']['user_id'] = 0;
        $params['filter']['filter']['status'] = erLhcoreClassModelChat::STATUS_CLOSED_CHAT;
        
        self::formatFilter($params['filter'], $sparams);
        
        if (! isset($params['filter']['filtergte']['time']) && ! isset($params['filter']['filterlte']['time'])) {
            $sparams['body']['query']['bool']['must'][]['range']['time']['gt'] = mktime(0, 0, 0, date('m'), date('d') - $params['days'], date('y')) * 1000;
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

    public static function statisticGetnumberofchatspermonth($params, $aggr = 'month')
    {
        $numberOfChats = array();
        
        $elasticSearchHandler = erLhcoreClassElasticClient::getHandler();
        
        $sparams = array();
        $sparams['index'] = erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionElasticsearch')->settings['index'];
        $sparams['type'] = erLhcoreClassModelESChat::$elasticType;
        $sparams['body']['size'] = 0;
        $sparams['body']['from'] = 0;
        $sparams['body']['aggs']['chats_over_time']['date_histogram']['field'] = 'time';
        $sparams['body']['aggs']['chats_over_time']['date_histogram']['interval'] = $aggr;
        
        $sparams['body']['aggs']['chats_over_time']['aggs']['status_aggr']['terms']['field'] = 'status';
        $sparams['body']['aggs']['chats_over_time']['aggs']['unanswered_aggr']['filter']['term']['unanswered_chat'] = 1;
        $sparams['body']['aggs']['chats_over_time']['aggs']['chat_initiator_aggr']['terms']['field'] = 'chat_initiator';
        
        $dateTime = new DateTime("now");
        $sparams['body']['aggs']['chats_over_time']['date_histogram']['time_zone'] = $dateTime->getOffset() / 60 / 60;
        
        $paramsOrig = $params;
        if ($aggr == 'month') {
            $paramsOrig['filter']['filtergt']['time'] = time() - (24 * 366 * 3600); // Limit results to one year
        } else {
            if (! isset($paramsOrig['filter']['filtergte']['time']) && ! isset($paramsOrig['filter']['filterlte']['time'])) {
                $paramsOrig['filter']['filtergt']['time'] = mktime(0, 0, 0, date('m'), date('d') - 31, date('y'));
            }
        }
        
        self::formatFilter($paramsOrig['filter'], $sparams);
        
        $response = $elasticSearchHandler->search($sparams);
        
        $keyStatus = array(
            erLhcoreClassModelChat::STATUS_CLOSED_CHAT => 'closed',
            erLhcoreClassModelChat::STATUS_ACTIVE_CHAT => 'active',
            erLhcoreClassModelChat::STATUS_OPERATORS_CHAT => 'operators',
            erLhcoreClassModelChat::STATUS_PENDING_CHAT => 'pending'
        );
        
        $keyStatusInit = array(
            erLhcoreClassModelChat::CHAT_INITIATOR_DEFAULT => 'chatinitdefault',
            erLhcoreClassModelChat::CHAT_INITIATOR_PROACTIVE => 'chatinitproact'
        );
        
        foreach ($response['aggregations']['chats_over_time']['buckets'] as $bucket) {
            $keyDateUnix = $bucket['key'] / 1000;
            
            foreach ($bucket['status_aggr']['buckets'] as $bucketStatus) {
                if (isset($keyStatus[$bucketStatus['key']])) {
                    $numberOfChats[$keyDateUnix][$keyStatus[$bucketStatus['key']]] = $bucketStatus['doc_count'];
                }
            }
            
            $numberOfChats[$keyDateUnix]['unanswered'] = $bucket['unanswered_aggr']['doc_count'];
            
            foreach ($bucket['chat_initiator_aggr']['buckets'] as $bucketStatus) {
                if (isset($keyStatusInit[$bucketStatus['key']])) {
                    $numberOfChats[$keyDateUnix][$keyStatusInit[$bucketStatus['key']]] = $bucketStatus['doc_count'];
                }
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
        }
        
        $sparams = array();
        $sparams['index'] = erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionElasticsearch')->settings['index'];
        $sparams['type'] = erLhcoreClassModelESMsg::$elasticType;
        $sparams['body']['size'] = 0;
        $sparams['body']['from'] = 0;
        $sparams['body']['aggs']['chats_over_time']['date_histogram']['field'] = 'time';
        $sparams['body']['aggs']['chats_over_time']['date_histogram']['interval'] = $aggr;
        
        $sparams['body']['aggs']['chats_over_time']['aggs']['msg_user']['filter']['term']['user_id'] = 0;
        $sparams['body']['aggs']['chats_over_time']['aggs']['msg_system']['filter']['terms']['user_id'] = array(
            - 1,
            - 2
        );
        
        $dateTime = new DateTime("now");
        $sparams['body']['aggs']['chats_over_time']['date_histogram']['time_zone'] = $dateTime->getOffset() / 60 / 60;
        
        $paramsOrig = $params;
        
        if ($aggr == 'month') {
            $paramsOrig['filter']['filtergt']['time'] = time() - (24 * 366 * 3600); // Limit results to one year
        } else {
            if (! isset($paramsOrig['filter']['filtergte']['time']) && ! isset($paramsOrig['filter']['filterlte']['time'])) {
                $paramsOrig['filter']['filtergt']['time'] = mktime(0, 0, 0, date('m'), date('d') - 31, date('y'));
            }
        }
        
        self::formatFilter($paramsOrig['filter'], $sparams);
        
        $response = $elasticSearchHandler->search($sparams);
        
        foreach ($response['aggregations']['chats_over_time']['buckets'] as $bucket) {
            $keyDateUnix = $bucket['key'] / 1000;
            if (isset($numberOfChats[$keyDateUnix])) {
                $numberOfChats[$keyDateUnix]['msg_operator'] = $bucket['doc_count'] - $bucket['msg_user']['doc_count'] - $bucket['msg_system']['doc_count'];
                $numberOfChats[$keyDateUnix]['msg_user'] = $bucket['msg_user']['doc_count'];
                $numberOfChats[$keyDateUnix]['msg_system'] = $bucket['msg_system']['doc_count'];
            }
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
    public static function statisticGetnumberofchatswaittime($params)
    {
        $numberOfChats = array();
        
        $elasticSearchHandler = erLhcoreClassElasticClient::getHandler();
        
        $sparams = array();
        $sparams['index'] = erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionElasticsearch')->settings['index'];
        $sparams['type'] = erLhcoreClassModelESChat::$elasticType;
        $sparams['body']['size'] = 0;
        $sparams['body']['from'] = 0;
        $sparams['body']['aggs']['chats_over_time']['date_histogram']['field'] = 'time';
        $sparams['body']['aggs']['chats_over_time']['date_histogram']['interval'] = 'month';
        $sparams['body']['aggs']['chats_over_time']['aggs']['avg_wait_time']['avg']['field'] = 'wait_time';
        
        $dateTime = new DateTime("now");
        $sparams['body']['aggs']['chats_over_time']['date_histogram']['time_zone'] = $dateTime->getOffset() / 60 / 60;
        
        $paramsOrig = $params;
        $paramsOrig['filter']['filtergt']['time'] = time() - (24 * 366 * 3600); // Limit results to one year
        $paramsOrig['filter']['filtergt']['wait_time'] = 0;
        $paramsOrig['filter']['filterlt']['wait_time'] = 600;
        
        self::formatFilter($paramsOrig['filter'], $sparams);
        
        $response = $elasticSearchHandler->search($sparams);
        
        foreach ($response['aggregations']['chats_over_time']['buckets'] as $bucket) {
            $numberOfChats[$bucket['key'] / 1000] = (int) $bucket['avg_wait_time']['value'];
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
        $sparams['index'] = erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionElasticsearch')->settings['index'];
        $sparams['type'] = erLhcoreClassModelESMsg::$elasticType;
        $sparams['body']['size'] = 0;
        $sparams['body']['from'] = 0;
        
        $useTimeFilter = ! isset($params['filter']['filtergte']['time']) && ! isset($params['filter']['filterlte']['time']);
        
        if ($useTimeFilter == true) {
            $params['filter']['filtergt']['time'] = mktime(0, 0, 0, date('m'), date('d') - $params['days'], date('y'));
        }
        
        self::formatFilter($params['filter'], $sparams);
        
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
        
        if (empty($params['filter'])) {
            $params['filter']['filtergt']['time'] = $dateUnixPast = mktime(0, 0, 0, date('m'), date('d') - $params['days'], date('y'));
        }
        
        $sparams = array();
        $sparams['index'] = erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionElasticsearch')->settings['index'];
        $sparams['type'] = erLhcoreClassModelESChat::$elasticType;
        $sparams['body']['aggs']['avg_wait_time']['avg']['field'] = 'chat_duration';
        $sparams['body']['size'] = 0;
        $sparams['body']['from'] = 0;
        
        $params['filter']['filtergt']['user_id'] = 0;
        $params['filter']['filtergt']['chat_duration'] = 0;
        $params['filter']['filterlt']['chat_duration'] = 60*60;
        $params['filter']['filter']['status'] = erLhcoreClassModelChat::STATUS_CLOSED_CHAT;
        
        self::formatFilter($params['filter'], $sparams);
        
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
        $sparams['index'] = erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionElasticsearch')->settings['index'];
        $sparams['type'] = erLhcoreClassModelESChat::$elasticType;
        
        self::formatFilter($params['filter'], $sparams);
        
        $sparams['body']['size'] = 0;
        $sparams['body']['from'] = 0;
        $sparams['body']['aggs']['group_by_hour']['terms']['field'] = 'hour';
        $sparams['body']['aggs']['group_by_hour']['terms']['size'] = 48;
        
        $response = $elasticSearchHandler->search($sparams);
        
        $numberOfChats = array_fill(1, 23, 0);

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

            $numberOfChats[$hourAdjusted] = $item['doc_count'];
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
        $sparams['index'] = erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionElasticsearch')->settings['index'];
        $sparams['type'] = erLhcoreClassModelESChat::$elasticType;
        
        $sparamsItem = $sparams;
        $paramsOrig = $params;
        self::formatFilter($paramsOrig['filter'], $sparamsItem);
        $numberOfChats['totalchats'] = erLhcoreClassModelESChat::getCount($sparamsItem);
        
        $sparamsItem = $sparams;
        $paramsOrig = $params;
        $paramsOrig['filter']['filter']['status'] = erLhcoreClassModelChat::STATUS_PENDING_CHAT;
        self::formatFilter($paramsOrig['filter'], $sparamsItem);
        $numberOfChats['totalpendingchats'] = erLhcoreClassModelESChat::getCount($sparamsItem);
        
        $sparamsItem = $sparams;
        $paramsOrig = $params;
        $paramsOrig['filter']['filter']['status'] = erLhcoreClassModelChat::STATUS_ACTIVE_CHAT;
        self::formatFilter($paramsOrig['filter'], $sparamsItem);
        $numberOfChats['total_active_chats'] = erLhcoreClassModelESChat::getCount($sparamsItem);
        
        $sparamsItem = $sparams;
        $paramsOrig = $params;
        $paramsOrig['filter']['filter']['status'] = erLhcoreClassModelChat::STATUS_CLOSED_CHAT;
        self::formatFilter($paramsOrig['filter'], $sparamsItem);
        $numberOfChats['total_closed_chats'] = erLhcoreClassModelESChat::getCount($sparamsItem);
        
        $sparamsItem = $sparams;
        $paramsOrig = $params;
        $paramsOrig['filter']['filter']['unanswered_chat'] = 1;
        self::formatFilter($paramsOrig['filter'], $sparamsItem);
        $numberOfChats['total_unanswered_chat'] = erLhcoreClassModelESChat::getCount($sparamsItem);
        
        $sparamsItem = $sparams;
        $paramsOrig = $params;
        $paramsOrig['filter']['filter']['status'] = erLhcoreClassModelChat::STATUS_CHATBOX_CHAT;
        self::formatFilter($paramsOrig['filter'], $sparamsItem);
        $numberOfChats['chatbox_chats'] = erLhcoreClassModelESChat::getCount($sparamsItem);
        
        $sparamsItem = $sparams;
        $paramsOrig = $params;
        self::formatFilter($paramsOrig['filter'], $sparamsItem);
        $sparamsItem['type'] = erLhcoreClassModelESMsg::$elasticType;
        $numberOfChats['ttmall'] = erLhcoreClassModelESMsg::getCount($sparamsItem);
        
        $sparamsItem = $sparams;
        $paramsOrig = $params;
        $paramsOrig['filter']['filter']['user_id'] = 0;
        self::formatFilter($paramsOrig['filter'], $sparamsItem);
        $sparamsItem['type'] = erLhcoreClassModelESMsg::$elasticType;
        $numberOfChats['ttmvis'] = erLhcoreClassModelESMsg::getCount($sparamsItem);
        
        $sparamsItem = $sparams;
        $paramsOrig = $params;
        $paramsOrig['filter']['filterin']['user_id'] = array(
            - 1,
            - 2
        );
        self::formatFilter($paramsOrig['filter'], $sparamsItem);
        $sparamsItem['type'] = erLhcoreClassModelESMsg::$elasticType;
        $numberOfChats['ttmsys'] = erLhcoreClassModelESMsg::getCount($sparamsItem);
        
        $numberOfChats['ttmop'] = $numberOfChats['ttmall'] - $numberOfChats['ttmvis'] - $numberOfChats['ttmsys'];
        
        return array(
            'status' => erLhcoreClassChatEventDispatcher::STOP_WORKFLOW,
            'list' => $numberOfChats
        );
    }

    public static function statisticGetnumberofchatswaittimeperday($params)
    {
        $elasticSearchHandler = erLhcoreClassElasticClient::getHandler();
        
        $numberOfChats = array();
        
        $sparams = array();
        $sparams['index'] = erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionElasticsearch')->settings['index'];
        $sparams['type'] = erLhcoreClassModelESChat::$elasticType;
        
        self::formatFilter($params['filter'], $sparams);
        
        if (! isset($params['filter']['filtergte']['time']) && ! isset($params['filter']['filterlte']['time'])) {
            $sparams['body']['query']['bool']['must'][]['range']['time']['gt'] = mktime(0, 0, 0, date('m'), date('d') - 31, date('y')) * 1000;
        }
        
        $sparams['body']['size'] = 0;
        $sparams['body']['from'] = 0;
        $sparams['body']['aggs']['chats_over_time']['date_histogram']['field'] = 'time';
        $sparams['body']['aggs']['chats_over_time']['date_histogram']['interval'] = 'day';
        $sparams['body']['aggs']['chats_over_time']['aggs']['avg_wait_time']['avg']['field'] = 'wait_time';
        
        $dateTime = new DateTime("now");
        $sparams['body']['aggs']['chats_over_time']['date_histogram']['time_zone'] = $dateTime->getOffset() / 60 / 60;
        
        $sparams['body']['query']['bool']['must'][]['range']['wait_time']['gt'] = 0;
        $sparams['body']['query']['bool']['must'][]['range']['wait_time']['lt'] = 600;
        
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
        $sparams['index'] = erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionElasticsearch')->settings['index'];
        $sparams['type'] = erLhcoreClassModelESChat::$elasticType;
        
        self::formatFilter($params['filter'], $sparams);
        
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
        $sparams['body']['aggs']['chat_count']['aggs']['abandoned_chats']['filter']['bool']['must'][]['term']['status_sub'] = erLhcoreClassModelChat::STATUS_SUB_USER_CLOSED_CHAT;
        
        $result = $elasticSearchHandler->search($sparams);
        
        foreach ($result['aggregations']['chat_count']['buckets'] as $key => $bucket) {
            
            $stats['rows'][] = array(
                'from' => $bucket['from'],
                'to' => isset($bucket['to']) ? $bucket['to'] : false,
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
        $sparams['index'] = erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionElasticsearch')->settings['index'];
        $sparams['type'] = erLhcoreClassModelESChat::$elasticType;
        
        $params['filter']['filterin']['user_id'] = array_keys($params['user_list']);
        
        self::formatFilter($params['filter'], $sparams);
        
        if (! isset($params['filter']['filtergte']['time']) && ! isset($params['filter']['filterlte']['time'])) {
            $sparams['body']['query']['bool']['must'][]['range']['time']['gt'] = mktime(0, 0, 0, date('m'), date('d') - $params['days'], date('y')) * 1000;
        }
        
        $sparams['body']['size'] = 0;
        $sparams['body']['from'] = 0;
        $sparams['body']['aggs']['group_by_user']['terms']['field'] = 'user_id';
        $sparams['body']['aggs']['group_by_user']['terms']['size'] = 1000;
        
        // $filterOnline['filter']['usaccept'] = 0; erLhcoreClassChatStatistic::numberOfChatsDialogsByUser(30,$filterOnline);
        $sparams['body']['aggs']['group_by_user']['aggs']['us_accept']['filter']['term']['usaccept'] = 0;
        
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

            $statsValue = array(
                'total_chats' => $bucket['doc_count'],
                'total_chats_usaccept' => $bucket['us_accept']['doc_count'],
                'chat_duration_sum' => $bucket['closed_chats']['chat_duration_sum']['value'],
                'chat_duration_avg' => $bucket['closed_chats']['chat_duration_avg']['value'],
                'wait_time' => $bucket['avg_wait_time_filter']['wait_time']['value']
            );

            // Append extension custom aggregation
            erLhcoreClassChatEventDispatcher::getInstance()->dispatch('elasticsearch.getagentstatistic_chats_value', array(
                'bucket' => & $bucket,
                'stats_value' => & $statsValue
            ));

            $usersStats[$bucket['key']] = $statsValue;
        }

        // Online hours aggregration
        $sparams = array();
        $sparams['index'] = erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionElasticsearch')->settings['index'];
        $sparams['type'] = erLhcoreClassModelESOnlineSession::$elasticType;
        $params['filter']['filterin']['user_id'] = array_keys($params['user_list']);
        
        // Remove department filter
        $filterOnlineHours = $params['filter'];
        if (isset($filterOnlineHours['filter']['dep_id'])) {
            unset($filterOnlineHours['filter']['dep_id']);
        }
        
        if (isset($filterOnlineHours['filterin']['dep_id'])) {
            unset($filterOnlineHours['filterin']['dep_id']);
        }
        
        self::formatFilter($filterOnlineHours, $sparams);
        
        if (! isset($params['filter']['filtergte']['time']) && ! isset($params['filter']['filterlte']['time'])) {
            $sparams['body']['query']['bool']['must'][]['range']['time']['gt'] = mktime(0, 0, 0, date('m'), date('d') - $params['days'], date('y')) * 1000;
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
        
        $list = array();
        
        foreach ($params['user_list'] as $user) {
            $agentName = $user->name;
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
            $avgDuration = isset($usersStats[$user->id]['chat_duration_avg']) ? $usersStats[$user->id]['chat_duration_avg'] : 0; //

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
                'avgChatLengthSeconds' => $avgDuration
            );

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
        $sparams['index'] = erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionElasticsearch')->settings['index'];
        $sparams['type'] = erLhcoreClassModelESChat::$elasticType;
        $sparams['body']['size'] = 0;
        $sparams['body']['from'] = 0;
        
        $useTimeFilter = ! isset($params['filter']['filtergte']['time']) && ! isset($params['filter']['filterlte']['time']);
        
        if ($useTimeFilter == true) {
            $params['filter']['filtergt']['time'] = mktime(0, 0, 0, date('m'), date('d') - $params['days'], date('y'));
        }
        
        self::formatFilter($params['filter'], $sparams);
        
        $sparams['body']['aggs']['group_by_user']['terms']['field'] = 'user_id';
        $sparams['body']['aggs']['group_by_user']['terms']['size'] = $params['limit'];
        $sparams['body']['aggs']['group_by_user']['aggs']['fb_status']['terms']['field'] = 'fbst';
        $sparams['body']['aggs']['group_by_user']['aggs']['fb_status']['terms']['size'] = 5;
        
        // Get grouped results
        $response = $elasticSearchHandler->search($sparams);
        
        $items = array();
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
            
            $sparams['type'] = erLhcoreClassModelESMsg::$elasticType;
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

    public static function formatFilter($params, & $sparams)
    {
        $returnFilter = array();
        
        foreach ($params as $type => $params) {
            foreach ($params as $field => $value) {
                
                $field = str_replace('lh_chat.', '', $field);
                
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
                    $sparams['body']['query']['bool']['must'][]['terms'][$field] = $value;
                } elseif ($type == 'filterm') {
                    $sparams['body']['query']['bool']['must'][]['term'][$field] = $value;
                } elseif ($type == 'filterinm') {
                    $sparams['body']['query']['bool']['must'][]['terms'][$field] = $value;
                }

            }
        }
        
        return $returnFilter;
    }
}