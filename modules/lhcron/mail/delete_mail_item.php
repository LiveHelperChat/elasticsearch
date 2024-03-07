<?php

/*
 * php cron.php -s site_admin -e elasticsearch -c cron/mail/delete_mail_item
 *
 * */
$fp = fopen("cache/cron_mail_delete_mail_item_elastic.lock", "w+");

// Gain the lock
if (!flock($fp, LOCK_EX | LOCK_NB)) {
    echo "Couldn't get the lock! Another process is already running\n";
    fclose($fp);
    exit;
} else {
    echo "Lock acquired. Starting process!\n";
}

$cfg = erConfigClassLhConfig::getInstance();
$worker = $cfg->getSetting( 'webhooks', 'worker' );

if (\LiveHelperChatExtension\elasticsearch\providers\Delete\DeleteItem::estimateRows() > 0)
{
    if ($worker == 'resque' && class_exists('erLhcoreClassExtensionLhcphpresque')) {
        if (erLhcoreClassRedis::instance()->llen('resque:queue:lhc_mailconv_delete_elastic') <= 4) {
            erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionLhcphpresque')->enqueue('lhc_mailconv_delete_elastic', '\LiveHelperChatExtension\elasticsearch\providers\Delete\DeleteWorker', array('is_background' => true));
        }
    } else {
        $deleteWorker = new \LiveHelperChatExtension\elasticsearch\providers\Delete\DeleteWorker();
        $deleteWorker->perform();
    }
}

flock($fp, LOCK_UN); // release the lock
fclose($fp);

?>
