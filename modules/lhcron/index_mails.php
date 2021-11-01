<?php

// /usr/bin/php cron.php -s site_admin -e elasticsearch -c cron/index_mails -p <last_mail_id>

$esOptions = erLhcoreClassModelChatConfig::fetch('elasticsearch_options');
$dataOptions = (array)$esOptions->data;

if (isset($dataOptions['disable_es']) && $dataOptions['disable_es'] == 1) {
    echo "Elastic Search is disabled!\n";
    exit;
}

if (is_numeric($cronjobPathOption->value)) {
    $lastId = (int)$cronjobPathOption->value;
} else {
    $lastId = 0;
}

echo "Indexing mails\n";

$pageLimit = 500;

for ($i = 0; $i < 1000000; $i++) {

    echo "Saving mails - ",($i + 1),"\n";

    $chats = erLhcoreClassModelMailconvMessage::getList(array('offset' => 0, 'filtergt' => array('id' => $lastId), 'limit' => $pageLimit, 'sort' => 'id ASC'));

    if (!empty($chats))
    {
        end($chats);
        $lastChat = current($chats);

        $lastId = $lastChat->id;

        echo $lastId,'-',count($chats),"\n";

        if (empty($chats)){
            exit;
        }

        erLhcoreClassElasticSearchIndex::indexMails(array('mails' => $chats));
    } else {
        echo "No mails to index!\n";
        exit;
    }
}

?>
