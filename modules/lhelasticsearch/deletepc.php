<?php

if (!$currentUser->validateCSFRToken($Params['user_parameters_unordered']['csfr'])) {
    die('Invalid CSFR Token');
    exit;
}

$chat = erLhcoreClassModelESPendingChat::fetch($Params['user_parameters']['id']);
$chat->removeThis();

erLhcoreClassModule::redirect('elasticsearch/listpc');
exit();
