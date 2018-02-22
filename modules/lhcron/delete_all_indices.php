<?php

// /usr/bin/php cron.php -s site_admin -e elasticsearch -c cron/delete_all_indices

echo "Deleting all indices\n";

foreach (erLhcoreClassElasticClient::getHandler()->indices()->getAliases(array('index' => 'chat*')) as $indice => $data) {

    echo "Deleting - ",$indice,"\n";
   erLhcoreClassElasticClient::getHandler()->indices()->delete(array(
        'index' =>$indice
   ));
}

