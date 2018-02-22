<?php

// /usr/bin/php cron.php -s site_admin -e elasticsearch -c cron/index_archive_chats -p <archive_id>_<last_chat_id>

list($archiveId, $lastId) = explode('_',$cronjobPathOption->value);

$archive = erLhcoreClassModelChatArchiveRange::fetch($archiveId);
$archive->setTables();

echo "Indexing archive chats\n";

$pageLimit = 500;

for ($i = 0; $i < 1000000; $i++) {

    echo "Saving chats - ",($i + 1),"\n";

    $chats = erLhcoreClassModelChatArchive::getList(array('offset' => 0, 'filtergt' => array('id' => $lastId), 'limit' => $pageLimit, 'sort' => 'id ASC'));

    if (!empty($chats))
    {
        end($chats);
        $lastChat = current($chats);

        $lastId = $lastChat->id;

        echo $lastId,'-',count($chats),"\n";

        if (empty($chats)) {
            exit;
        }

        erLhcoreClassElasticSearchIndex::indexChats(array('chats' => $chats, 'archive' => true));
    } else {
        echo "No chats to index!\n";
        exit;
    }
}

?>
