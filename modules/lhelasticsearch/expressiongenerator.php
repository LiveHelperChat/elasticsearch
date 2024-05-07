<?php

$tpl = erLhcoreClassTemplate::getInstance('elasticsearch/expressiongenerator.tpl.php');
$tpl->set('scope',$Params['user_parameters']['scope']);
echo $tpl->fetch();
exit;
