<?php

// Run once a day
// /usr/bin/php cron.php -s site_admin -e elasticsearch -c cron/index_precreate

$esOptions = erLhcoreClassModelChatConfig::fetch('elasticsearch_options');
$dataOptions = (array)$esOptions->data;

if (isset($dataOptions['disable_es']) && $dataOptions['disable_es'] == 1) {
    echo "Elastic Search is disabled!\n";
    exit;
}

echo "Creating index\n";

$esOptions = erLhcoreClassModelChatConfig::fetch('elasticsearch_options');
$dataOptions = (array)$esOptions->data;

$settings = include ('extension/elasticsearch/settings/settings.ini.php');

$indexSave = null;

if ($dataOptions['index_type'] == 'daily') {
    $indexSave = $settings['index'];
    $indexPrepend = date('Y.m.d',time()+24*3600);
} elseif ($dataOptions['index_type'] == 'monthly') {
    $indexSave = $settings['index'];
    $indexPrepend = date('Y.m',time()+24*3600);
}

if ($indexSave !== null) {
    $sessionElasticStatistic = erLhcoreClassModelESChat::getSession();
    $esSearchHandler = erLhcoreClassElasticClient::getHandler();
    erLhcoreClassElasticClient::indexExists($esSearchHandler, $indexSave, $indexPrepend, true);
    echo "Created index - ",$indexSave,"\n";
}