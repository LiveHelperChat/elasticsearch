<?php
// Run me every 1 minute
// /usr/bin/php cron.php -s site_admin -e elasticsearch -c cron/check_health

$esOptions = erLhcoreClassModelChatConfig::fetch('elasticsearch_options');
$dataOptions = (array)$esOptions->data;

if (!(isset($dataOptions['disable_es']) && $dataOptions['disable_es'] == 1)) {

    try {
        erLhcoreClassElasticClient::getHandler()->indices()->getAliases(array('index' => 'chat*'));
        echo "Elastic Search is alive\n";
    } catch (Exception $e) {
        $dataOptions['disable_es'] = 1;
        $dataOptions['fail_reason'] = $e->getMessage();
        $esOptions->explain = '';
        $esOptions->type = 0;
        $esOptions->hidden = 1;
        $esOptions->identifier = 'elasticsearch_options';
        $esOptions->value = serialize($dataOptions);
        $esOptions->saveThis();
        echo "We found that elastic search is dead, informing!\n";
    }

} else {
    echo "Elastic Search is still dead, no status change!";
}

