<?php

// /usr/bin/php cron.php -s site_admin -e elasticsearch -c cron/update_indices

$esOptions = erLhcoreClassModelChatConfig::fetch('elasticsearch_options');
$dataOptions = (array)$esOptions->data;

if (isset($dataOptions['disable_es']) && $dataOptions['disable_es'] == 1) {
    echo "Elastic Search is disabled!\n";
    exit;
}

echo "Updating all indices\n";

foreach (erLhcoreClassElasticClient::getHandler()->indices()->getAliases(array('index' => 'chat*')) as $indice => $data) {
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
        'structure' => & $contentData,
        'index_original' => $settings['index'],
        'index_new' => $indice,
    ));

    erLhcoreClassElasticSearchUpdate::doElasticUpdate($contentData, $indice);

    echo "Updating - ",$indice,"\n";
}