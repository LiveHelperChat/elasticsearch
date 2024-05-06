<?php

// /usr/bin/php cron.php -s site_admin -e elasticsearch -c cron/index_ov_clean

$esOptions = erLhcoreClassModelChatConfig::fetch('elasticsearch_options');
$dataOptions = (array)$esOptions->data;

if (isset($dataOptions['disable_es']) && $dataOptions['disable_es'] == 1) {
    echo "Elastic Search is disabled!\n";
    exit;
}

echo "Removing old online visitors\n";

$sparams = array(
    'body' => array()
);

$sparams['body']['query']['bool']['must'][]['range']['last_visit']['lte'] = (time() - $dataOptions['days_ov'] * 24 * 3600) * 1000;

foreach (\LiveHelperChatExtension\elasticsearch\providers\Index\OnlineVisitor::getList(array('body' => $sparams['body'], 'offset' => 0, 'limit' => 1000)) as $item) {
    echo "Removing - ",$item->id,"\n";
    $item->removeThis();
}
