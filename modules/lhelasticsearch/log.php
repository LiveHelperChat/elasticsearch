<?php

$tpl = erLhcoreClassTemplate::getInstance( 'elasticsearch/log.tpl.php');

\LiveHelperChatExtension\elasticsearch\providers\Index\RestLog::getSession();

$item = \LiveHelperChatExtension\elasticsearch\providers\Index\RestLog::fetch($Params['user_parameters']['id'], \LiveHelperChatExtension\elasticsearch\providers\Index\RestLog::$indexName . '-' . \LiveHelperChatExtension\elasticsearch\providers\Index\RestLog::$elasticType);

$tpl->set('item', $item);

echo $tpl->fetch();
exit;
?>