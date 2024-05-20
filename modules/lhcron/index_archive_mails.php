<?php

// /usr/bin/php cron.php -s site_admin -e elasticsearch -c cron/index_archive_mails -p <archive_id>_<last_mail_id>

$esOptions = erLhcoreClassModelChatConfig::fetch('elasticsearch_options');
$dataOptions = (array)$esOptions->data;

if (isset($dataOptions['disable_es']) && $dataOptions['disable_es'] == 1) {
    echo "Elastic Search is disabled!\n";
    exit;
}

list($archiveId, $lastId) = explode('_',$cronjobPathOption->value);

$archive = \LiveHelperChat\Models\mailConv\Archive\Range::fetch($archiveId);
$archive->setTables();

echo "Indexing archive mails\n";

$pageLimit = 500;

for ($i = 0; $i < 1000000; $i++) {

    echo "Saving mails - ",($i + 1),"\n";

    $chats = \LiveHelperChat\Models\mailConv\Archive\Message::getList(array('offset' => 0, 'filtergt' => array('id' => $lastId), 'limit' => $pageLimit, 'sort' => 'id ASC'));

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
