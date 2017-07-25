<?php

if (!$currentUser->validateCSFRToken($Params['user_parameters_unordered']['csfr'])) {
    die('Invalid CSFR Token');
    exit;
}

$chat = erLhcoreClassModelESOnlineOperator::fetch($Params['user_parameters']['id']);
$chat->removeThis();

erLhcoreClassModule::redirect('elasticsearch/listop');
exit();
