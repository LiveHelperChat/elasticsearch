<?php

$action = (string) $Params['user_parameters_unordered']['action'];

$esOptions = erLhcoreClassModelChatConfig::fetch('elasticsearch_options');
$dataOptions = (array)$esOptions->data;
if (isset($dataOptions['disable_es']) && $dataOptions['disable_es'] == 1) {
    echo json_encode(array('result' => 'Elastic Search is disabled!'));
    exit;
}

if (in_array($action, array(
    'statuselastic',
    'updateelastic',
    'createelasticindex'
))) {
    
    $tpl = erLhcoreClassTemplate::getInstance('elasticsearch/statuselastic.tpl.php');

    $contentData = json_decode(file_get_contents('extension/elasticsearch/doc/structure_elastic.json'), true);

    $settings = erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionElasticsearch')->settings;

    $indexSave = $settings['index'];

    $indexPrepend = '';

    if ($dataOptions['index_type'] == 'daily') {
        $indexSave = $settings['index'];
        $indexPrepend = date('Y.m.d',time()+24*3600);
    } elseif ($dataOptions['index_type'] == 'yearly') {
        $indexSave = $settings['index'];
        $indexPrepend =  date('Y',time()+24*3600);
    } elseif ($dataOptions['index_type'] == 'monthly') {
        $indexSave = $settings['index'];
        $indexPrepend =  date('Y.m',time()+24*3600);
    }

    $elasticIndexExist = true;

    $missingIndexes = array();

    $types = array();

    if ($action == 'updateelastic' || $action == 'createelasticindex') {
        $sessionElasticStatistic = erLhcoreClassModelESChat::getSession();
        $esSearchHandler = erLhcoreClassElasticClient::getHandler();
        erLhcoreClassElasticClient::indexExists($esSearchHandler, $indexSave, $indexPrepend, true);
    }

    foreach ($contentData['types'] as $type => $mapping) {

        $elasticIndex = $indexSave . '-' . $type . ($indexPrepend != '' ? '-' . $indexPrepend : '');

        if (!erLhcoreClassElasticClient::getHandler()->indices()->exists(array(
            'index' => $elasticIndex
        ))) {
            $missingIndexes[] = $elasticIndex;
            $elasticIndexExist = false;
        } else {

            erLhcoreClassChatEventDispatcher::getInstance()->dispatch('system.getelasticstructure', array(
                'structure' => & $contentData,
                'index_original' => $settings['index'],
                'index_new' => $elasticIndex,
                'type' => $type,
                'mapping' => & $mapping,
            ));

            if (isset($contentData[$elasticIndex]['types'][$type])) {
                $mapping = array_merge($mapping,$contentData[$elasticIndex]['types'][$type]);
            }

            if ($type == 'lh_chat' && isset($settings['columns']) && !empty($settings['columns'])) {
                foreach ($settings['columns'] as $field => $dataField) {
                    if (isset($dataField['type'])) {
                        $mapping[$field] = [
                            'type' => $dataField['type']
                        ];
                    }
                }
            }

            $types[] = erLhcoreClassElasticSearchUpdate::getElasticStatus($mapping, $elasticIndex);

        }
    }

    $tpl->set('typesGeneral', $types);
    $tpl->set('elasticIndexExist', $elasticIndexExist);
    $tpl->set('missingIndexes', $missingIndexes);

    echo json_encode(array(
        'result' => $tpl->fetch()
    ));
    exit();
}

?>