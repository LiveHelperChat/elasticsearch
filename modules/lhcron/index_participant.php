<?php

// /usr/bin/php cron.php -s site_admin -e elasticsearch -c cron/index_participant

$esOptions = erLhcoreClassModelChatConfig::fetch('elasticsearch_options');
$dataOptions = (array)$esOptions->data;

if (isset($dataOptions['disable_es']) && $dataOptions['disable_es'] == 1) {
    echo "Elastic Search is disabled!\n";
    exit;
}

echo "Indexing participants\n";

$pageLimit = 500;
$lastId = 0;

for ($i = 0; $i < 100000; $i++) {

    echo "Saving participant - ",($i + 1),"\n";

    $messages = \LiveHelperChat\Models\LHCAbstract\ChatParticipant::getList(array('offset' => 0, 'filtergt' => array('id' => $lastId), 'limit' => $pageLimit, 'sort' => 'id ASC'));
    end($messages);
    $lastMessage = current($messages);

    if (!is_object($lastMessage)) {
        exit;
    }

    $lastId = $lastMessage->id;

    echo $lastId,'-',count($messages),"\n";

    if (empty($messages)){
        exit;
    }

    erLhcoreClassElasticSearchIndex::indexParticipant(array('participant' => $messages));
}

?>