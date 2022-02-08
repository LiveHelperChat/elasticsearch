<?php

// Run once a day
// /usr/bin/php cron.php -s site_admin -e elasticsearch -c cron/index_precreate
// /usr/bin/php cron.php -s site_admin -e elasticsearch -c cron/index_precreate -p yearly

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
} elseif ($dataOptions['index_type'] == 'yearly') {
    $indexSave = $settings['index'];
    $indexPrepend = date('Y',time()+24*3600);
} elseif ($dataOptions['index_type'] == 'monthly') {
    $indexSave = $settings['index'];
    $indexPrepend = date('Y.m',time()+24*3600);
}

if ($indexSave == null) {
    echo "This script works only with dynamic index!\n";
    exit;
}

try {
    if (is_numeric($cronjobPathOption->value)) {
        if ($dataOptions['index_type'] == 'monthly') {
            for ($i = 1; $i <= 12; $i++) {
                $indexPrepend = $cronjobPathOption->value.'.'.($i < 10 ? '0'.$i : $i);
                $sessionElasticStatistic = erLhcoreClassModelESChat::getSession();
                $esSearchHandler = erLhcoreClassElasticClient::getHandler();
                erLhcoreClassElasticClient::indexExists($esSearchHandler, $indexSave, $indexPrepend, true);
                echo "Created index - ",$indexSave . '-' . $indexPrepend,"\n";
            }
        } else if ($dataOptions['index_type'] == 'yearly') {
            $indexSave = $settings['index'];
            $indexPrepend = $cronjobPathOption->value;
            $sessionElasticStatistic = erLhcoreClassModelESChat::getSession();
            $esSearchHandler = erLhcoreClassElasticClient::getHandler();
            erLhcoreClassElasticClient::indexExists($esSearchHandler, $indexSave, $indexPrepend, true);
            echo "Pre-creating yearly index - ",$indexSave . '-' . $indexPrepend,"\n";
        }
    } elseif ($cronjobPathOption->value == 'yearly') {
        $indexSave = $settings['index'];
        $indexPrepend = date('Y',time()+24*3600);
        $sessionElasticStatistic = erLhcoreClassModelESChat::getSession();
        $esSearchHandler = erLhcoreClassElasticClient::getHandler();
        erLhcoreClassElasticClient::indexExists($esSearchHandler, $indexSave, $indexPrepend, true);
        echo "Pre-creating yearly index - ",$indexSave . '-' . $indexPrepend,"\n";
    } else {
        if ($indexSave !== null) {
            $sessionElasticStatistic = erLhcoreClassModelESChat::getSession();
            $esSearchHandler = erLhcoreClassElasticClient::getHandler();
            erLhcoreClassElasticClient::indexExists($esSearchHandler, $indexSave, $indexPrepend, true);
            echo "Created index - ",$indexSave . '-' . $indexPrepend,"\n";
        }
    }

} catch (Exception $e) {

    $esOptions = erLhcoreClassModelChatConfig::fetch('elasticsearch_options');
    $dataOptions = (array)$esOptions->data;

    if (isset($dataOptions['report_email_es']) && !empty($dataOptions))
    {
        $mail = new PHPMailer();
        $mail->CharSet = "UTF-8";
        $mail->FromName = 'Live Helper Chat Elastic Search';
        $mail->Subject = 'Elastic Search could not create index';
        $mail->Body = "Elastic Search returned an error - \n" . $e->getMessage();

        $emailRecipient = explode(',',$dataOptions['report_email_es']);

        foreach ($emailRecipient as $receiver) {
            $mail->AddAddress( trim($receiver) );
        }

        erLhcoreClassChatMail::setupSMTP($mail);
        $mail->Send();
    }
}



