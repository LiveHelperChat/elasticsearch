<?php

if (!$currentUser->validateCSFRToken($Params['user_parameters_unordered']['csfr'])) {
    die('Invalid CSFR Token');
    exit;
}

$item = \LiveHelperChatExtension\elasticsearch\providers\Index\RestLog::fetch($Params['user_parameters']['id'], $Params['user_parameters']['index']);
$item->removeThis();

erLhcoreClassModule::redirect('elasticsearch/listlog');
exit();
