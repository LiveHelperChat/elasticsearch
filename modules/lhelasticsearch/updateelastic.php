<?php

$action = (string) $Params['user_parameters_unordered']['action'];

if (in_array($action, array(
    'statuselastic',
    'updateelastic',
    'createelasticindex'
))) {
    
    $tpl = erLhcoreClassTemplate::getInstance('elasticsearch/statuselastic.tpl.php');
    
    $elasticIndexExist = erLhcoreClassElasticSearchUpdate::elasticIndexExist();
    
    $contentData = file_get_contents('extension/elasticsearch/doc/structure_elastic.json');
    
    $settings = erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionElasticsearch')->settings;
        
    $contentAdditionalData = array();
    foreach ($settings['additional_indexes'] as $key => $index) {
        if (file_exists('extension/elasticsearch/doc/update_elastic/structure_' . $key . '.json')) {
            $content = file_get_contents('doc/update_elastic/structure_' . $key . '.json');
            $contentAdditionalData[$index] = json_decode($content, true);
        }
    }
    
    $contentData = array_merge_recursive(array(
        $settings['index'] => json_decode($contentData, true)
    ), $contentAdditionalData);
    
    erLhcoreClassChatEventDispatcher::getInstance()->dispatch('system.getelasticstructure', array(
        'structure' => & $contentData
    ));
    
    if ($elasticIndexExist == true) {
        
        if ($action == 'updateelastic') {
            erLhcoreClassElasticSearchUpdate::doElasticUpdate($contentData);
        }
    } else {
        
        if ($action == 'createelasticindex') {
            erLhcoreClassElasticSearchUpdate::doCreateElasticIndex();
            $elasticIndexExist = erLhcoreClassElasticSearchUpdate::elasticIndexExist();
        }
    }
    
    if ($elasticIndexExist == true) {
        
        $types = erLhcoreClassElasticSearchUpdate::getElasticStatus($contentData);
        $tpl->set('types', $types);
    }
    
    $tpl->set('elasticIndexExist', $elasticIndexExist);
    
    echo json_encode(array(
        'result' => $tpl->fetch()
    ));
    exit();
}

?>