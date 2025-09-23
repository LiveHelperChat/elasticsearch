<?php

if (!$currentUser->validateCSFRToken($Params['user_parameters_unordered']['csfr'])) {
    die('Invalid CSFR Token');
    exit;
}

try {
    erLhcoreClassElasticClient::getHandler()->indices()->forcemerge(array(
        'index' => $Params['user_parameters']['indice'],
        'only_expunge_deletes' => true,
        'wait_for_completion' => false,
        'ignore_unavailable' => true
    ));
} catch (Exception $e) {
    erLhcoreClassElasticClient::getHandler()->indices()->forcemerge(array(
        'index' => $Params['user_parameters']['indice'],
        'only_expunge_deletes' => true,
        'ignore_unavailable' => true
    ));
}


erLhcoreClassModule::redirect('elasticsearch/indices');
exit();
