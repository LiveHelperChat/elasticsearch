<?php

// /usr/bin/php cron.php -s site_admin -e elasticsearch -c cron/index_ov

$esOptions = erLhcoreClassModelChatConfig::fetch('elasticsearch_options');
$dataOptions = (array)$esOptions->data;

if (isset($dataOptions['disable_es']) && $dataOptions['disable_es'] == 1) {
    echo "Elastic Search is disabled!\n";
    exit;
}

echo "Indexing online visitors\n";

$pageLimit = 500;

$parts = ceil(erLhcoreClassModelChatOnlineUser::getCount(['filtergt' => ['last_visit' => time() - $dataOptions['days_ov'] * 24 * 3600] ])/$pageLimit);

for ($i = 0; $i < $parts; $i++) {

    echo "Saving online visitor page - ",($i + 1),"\n";

    $items = erLhcoreClassModelChatOnlineUser::getList(array('filtergt' => ['last_visit' => time() - $dataOptions['days_ov'] * 24 * 3600], 'offset' => $i*$pageLimit, 'limit' => $pageLimit, 'sort' => 'id ASC'));

    erLhcoreClassElasticSearchIndex::indexOnlineVisitors(array('items' => $items));
}