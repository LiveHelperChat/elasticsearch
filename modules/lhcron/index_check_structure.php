<?php

// Run once a day
// /usr/bin/php cron.php -s site_admin -e elasticsearch -c cron/index_check_structure

$esOptions = erLhcoreClassModelChatConfig::fetch('elasticsearch_options');
$dataOptions = (array)$esOptions->data;

if (isset($dataOptions['disable_es']) && $dataOptions['disable_es'] == 1) {
    echo "Elastic Search is disabled!\n";
    exit;
}

echo "Updating index structure\n";

$esOptions = erLhcoreClassModelChatConfig::fetch('elasticsearch_options');
$dataOptions = (array)$esOptions->data;

$settings = include ('extension/elasticsearch/settings/settings.ini.php');

$settings['index'];

$indexSave = null;

echo "Checking structure for today indexes\n";

if ($dataOptions['index_type'] == 'daily') {
    $indexSave = $settings['index'] . date('Y.m.d',time());
} elseif ($dataOptions['index_type'] == 'yearly') {
    $indexSave = $settings['index'] . date('Y',time());
} elseif ($dataOptions['index_type'] == 'monthly') {
    $indexSave = $settings['index'] . date('Y.m',time());
}

if ($indexSave !== null) {
    $sessionElasticStatistic = erLhcoreClassModelESChat::getSession();
    $esSearchHandler = erLhcoreClassElasticClient::getHandler();
    erLhcoreClassElasticClient::indexExists($esSearchHandler, $indexSave, true);
    echo "Created/updated index - ",$indexSave,"\n";
}

echo "Checking structure for tomorrow indexes\n";

if ($dataOptions['index_type'] == 'daily') {
    $indexSave = $settings['index'] . date('Y.m.d',time()+24*3600);
} elseif ($dataOptions['index_type'] == 'yearly') {
    $indexSave = $settings['index'] . date('Y',time()+24*3600);
} elseif ($dataOptions['index_type'] == 'monthly') {
    $indexSave = $settings['index'] . date('Y.m',time()+24*3600);
}

if ($indexSave !== null) {
    $sessionElasticStatistic = erLhcoreClassModelESChat::getSession();
    $esSearchHandler = erLhcoreClassElasticClient::getHandler();
    erLhcoreClassElasticClient::indexExists($esSearchHandler, $indexSave, true);
    echo "Created/updated index - ",$indexSave,"\n";
}