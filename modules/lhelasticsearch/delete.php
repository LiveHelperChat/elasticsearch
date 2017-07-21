<?php

if (!$currentUser->validateCSFRToken($Params['user_parameters_unordered']['csfr'])) {
    die('Invalid CSFR Token');
    exit;
}

$chat = erLhcoreClassModelESChat::fetch($Params['user_parameters']['id']);
$chat->removeThis();

erLhcoreClassModule::redirect('elasticsearch/list');
exit();
