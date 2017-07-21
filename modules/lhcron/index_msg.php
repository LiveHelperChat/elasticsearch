<?php

// /usr/bin/php cron.php -s site_admin -e elasticsearch -c cron/index_msg

echo "Indexing chats\n";

$pageLimit = 500;

$parts = ceil(erLhcoreClassChat::getCount(array(),'lh_msg')/$pageLimit);

for ($i = 0; $i < $parts; $i++) {

    echo "Saving msg - ",($i + 1),"\n";

    erLhcoreClassElasticSearchIndex::indexMessages(array('messages' => erLhcoreClassModelmsg::getList(array('offset' => $i*$pageLimit, 'limit' => $pageLimit, 'sort' => 'id ASC'))));
}

?>