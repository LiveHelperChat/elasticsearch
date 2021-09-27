<?php
// /usr/bin/php cron.php -s site_admin -e elasticsearch -c cron/test/test_resque

erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionLhcphpresque')->enqueue('lhc_elastic_queue', 'erLhcoreClassElasticSearchWorker', array());

?>