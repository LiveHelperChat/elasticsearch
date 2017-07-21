<?php

if (!$currentUser->validateCSFRToken($Params['user_parameters_unordered']['csfr'])) {
    die('Invalid CSFR Token');
    exit;
}

$chat = erLhcoreClassModelESOnlineSession::fetch($Params['user_parameters']['id']);
$chat->removeThis();

erLhcoreClassModule::redirect('elasticsearch/listos');
exit();
