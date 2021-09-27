<?php
// Run me every 5 minutes
// /usr/bin/php cron.php -s site_admin -e elasticsearch -c cron/cron_mail

$esOptions = erLhcoreClassModelChatConfig::fetch('elasticsearch_options');
$data = (array)$esOptions->data;

if (isset($data['disable_es_mail']) && $data['disable_es_mail'] == 1) {
    echo "Elastic Search is disabled!\n";
    exit;
}

$resque = new erLhcoreClassElasticSearchWorker();
$resque->indexMails();
$resque->indexConversations();
$resque->indexDeleteMail();

?>