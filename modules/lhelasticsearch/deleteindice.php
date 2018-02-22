<?php

if (!$currentUser->validateCSFRToken($Params['user_parameters_unordered']['csfr'])) {
    die('Invalid CSFR Token');
    exit;
}

erLhcoreClassElasticClient::getHandler()->indices()->delete(array(
    'index' => $Params['user_parameters']['indice']
));

erLhcoreClassModule::redirect('elasticsearch/indices');
exit();
