<?php

if (!$currentUser->validateCSFRToken($Params['user_parameters_unordered']['csfr'])) {
    die('Invalid CSFR Token');
    exit;
}

$chat = erLhcoreClassModelESMsg::fetch($Params['user_parameters']['id']);
$chat->removeThis();

erLhcoreClassModule::redirect('elasticsearch/listmsg', '/' . $Params['user_parameters']['chat_id']);
exit();
