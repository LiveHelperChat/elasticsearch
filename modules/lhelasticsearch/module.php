<?php

$Module = array( "name" => "PHP-Resque",
				 'variable_params' => true );

$ViewList = array();

$ViewList['index'] = array(
    'params' => array(),
    'uparams' => array(),
    'functions' => array('configure')
);

$ViewList['list'] = array(
    'params' => array(),
    'uparams' => array(),
    'functions' => array('configure')
);

$ViewList['listos'] = array(
    'params' => array(),
    'uparams' => array(),
    'functions' => array('configure')
);

$ViewList['listpc'] = array(
    'params' => array(),
    'uparams' => array(),
    'functions' => array('configure')
);

$ViewList['listop'] = array(
    'params' => array(),
    'uparams' => array(),
    'functions' => array('configure')
);

$ViewList['options'] = array(
    'params' => array(),
    'uparams' => array(),
    'functions' => array('configure')
);

$ViewList['listmsg'] = array(
    'params' => array('chat_id'),
    'uparams' => array(),
    'functions' => array('configure')
);

$ViewList['elastic'] = array(
    'params' => array(),
    'uparams' => array(),
    'functions' => array('configure')
);

$ViewList['delete'] = array(
    'params' => array('id'),
    'uparams' => array('csfr'),
    'functions' => array('configure')
);

$ViewList['deleteop'] = array(
    'params' => array('id'),
    'uparams' => array('csfr'),
    'functions' => array('configure')
);

$ViewList['deleteos'] = array(
    'params' => array('id'),
    'uparams' => array('csfr'),
    'functions' => array('configure')
);

$ViewList['deletepc'] = array(
    'params' => array('id'),
    'uparams' => array('csfr'),
    'functions' => array('configure')
);

$ViewList['deletemsg'] = array(
    'params' => array('chat_id','id'),
    'uparams' => array('csfr'),
    'functions' => array('configure')
);

$ViewList['updateelastic'] = array(
    'params' => array(),
    'uparams' => array('action'),
    'functions' => array('configure')
);

$FunctionList['use'] = array('explain' => 'Allow operator to use PHP-Resque module');
$FunctionList['configure'] = array('explain' => 'Allow operator to configure PHP-Resque module');