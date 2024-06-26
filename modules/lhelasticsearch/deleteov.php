<?php

if (!$currentUser->validateCSFRToken($Params['user_parameters_unordered']['csfr'])) {
    die('Invalid CSFR Token');
    exit;
}

$chat = \LiveHelperChatExtension\elasticsearch\providers\Index\OnlineVisitor::fetch($Params['user_parameters']['id'], $Params['user_parameters']['index']);
$chat->removeThis();

erLhcoreClassModule::redirect('elasticsearch/listov');
exit();
