<?php

if (!$currentUser->validateCSFRToken($Params['user_parameters_unordered']['csfr'])) {
    die('Invalid CSFR Token');
    exit;
}

$mail = erLhcoreClassModelESMail::fetch($Params['user_parameters']['id'], $Params['user_parameters']['index']);
$mail->removeThis();

header('Location: ' . $_SERVER['HTTP_REFERER']);
exit();
