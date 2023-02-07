<?php

$Module = array( "name" => "Elastic Search",
				 'variable_params' => true );

$ViewList = array();

$ViewList['index'] = array(
    'params' => array(),
    'uparams' => array(),
    'functions' => array('configure')
);

$ViewList['interactions'] = array(
    'params' => array(),
    'uparams' => array('fuzzy_prefix','fuzzy','search_in','attr','val','timefrom','timefrom_minutes','timefrom_hours','timeto','timeto_minutes','timeto_hours','sort_chat','keyword'),
    'multiple_arguments' => array(
        'search_in',
    ),
);

$ViewList['list'] = array(
    'params' => array(),
    'uparams' => array('fuzzy_prefix','fuzzy','iwh_ids','timefromts','transfer_happened','not_invitation','proactive_chat','phone','chat_status_ids','hof','hvf','referrer','session_referrer','export','view','dropped_chat','abandoned_chat','has_unread_op_messages','cls_us','subject_id','bot_ids','has_operator','with_bot','without_bot','country_ids','attr_int_1_multi','attr_int_2_multi','attr_int_3_multi','attr_int_4_multi','no_user','uagent','exact_match','ds','department_ids','department_group_ids','user_ids','group_ids','invitation_id','attr_int_1','attr_int_2','attr_int_3','attr_int_4','attr_int_5','attr_int_6','attr_int_7','attr_int_8','attr_int_9','attr_int_10','attr_int_11','attr_int_12','attr_int_13','attr_int_14','attr_int_15','search_in','sort_chat','keyword','tab','chat_id','message_text','nick','sort_msg','email','user_id','department_id','timefrom','timefrom_minutes','timefrom_hours','timeto','timeto_minutes','timeto_hours','department_group_id','group_id','region'),
    'multiple_arguments' => array(
        'search_in',
        'department_ids',
        'department_group_ids',
        'user_ids',
        'group_ids',
        'attr_int_1_multi',
        'attr_int_2_multi',
        'attr_int_3_multi',
        'attr_int_4_multi',
        'country_ids',
        'bot_ids',
        'subject_id',
        'chat_status_ids',
        'region',
        'iwh_ids',
    ),
    'functions' => array('use')
);

$ViewList['listmail'] = array(
    'params' => array(),
    'uparams' => array(
        'attr_int_1_multi','attr_int_2_multi','attr_int_3_multi','attr_int_4_multi',
        'attr_int_1','attr_int_2','attr_int_3','attr_int_4','attr_int_5',
        'attr_int_6','attr_int_7','attr_int_8','attr_int_9','attr_int_10',
        'attr_int_11','attr_int_12','attr_int_13','attr_int_14','attr_int_15',
        'ds','search_in','sort_chat','keyword','conversation_id',
        'from_name','sort_msg','email', 'user_id', 'department_id',
        'timefrom', 'timefrom_minutes','timefrom_hours',
        'timeto','timeto_minutes', 'timeto_hours',
        'department_group_id','group_id', 'user_ids',
        'export','view','subject_id', 'department_ids',
        'department_group_ids','group_ids','hvf','response_type','status','status_conv',
        'sender_host','from_host','sender_address','is_followup','undelivered','lang_ids',
        'phone','opened','search_email_in','timefromts','fuzzy','fuzzy_prefix','status_conv_id',
        'status_msg_id','mailbox_ids'
    ),
    'multiple_arguments' => array(
        'search_in',
        'department_ids',
        'department_group_ids',
        'user_ids',
        'group_ids',
        'attr_int_1_multi',
        'attr_int_2_multi',
        'attr_int_3_multi',
        'attr_int_4_multi',
        'subject_id',
        'lang_ids',
        'status_conv_id',
        'status_msg_id',
        'mailbox_ids'
    ),
    'functions' => array('use')
);

$ViewList['raw'] = array(
    'params' => array('index','id'),
    'functions' => array('use')
);

$ViewList['rawmail'] = array(
    'params' => array('index','id'),
    'functions' => array('use')
);

$ViewList['getpreviouschats'] = array(
    'params' => array('chat_id'),
    'uparams' => array(),
    'functions' => array('use')
);

$ViewList['getpreviouschatsbyid'] = array(
    'params' => array('chat_id'),
    'uparams' => array('type'),
    'functions' => array('use')
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

$ViewList['indices'] = array(
    'params' => array(),
    'uparams' => array(),
    'functions' => array('configure')
);

$ViewList['deleteindice'] = array(
    'params' => array('indice'),
    'uparams' => array('csfr'),
    'functions' => array('configure')
);

$ViewList['getmapping'] = array(
    'params' => array('indice'),
    'uparams' => array(),
    'functions' => array('configure')
);

$ViewList['listmsg'] = array(
    'params' => array('chat_id'),
    'uparams' => array(),
    'functions' => array('use')
);

$ViewList['elastic'] = array(
    'params' => array(),
    'uparams' => array(),
    'functions' => array('configure')
);

$ViewList['delete'] = array(
    'params' => array('index','id'),
    'uparams' => array('csfr'),
    'functions' => array('configure')
);

$ViewList['deletemail'] = array(
    'params' => array('index','id'),
    'uparams' => array('csfr'),
    'functions' => array('configure')
);

$ViewList['deleteop'] = array(
    'params' => array('index','id'),
    'uparams' => array('csfr'),
    'functions' => array('configure')
);

$ViewList['deleteos'] = array(
    'params' => array('index','id'),
    'uparams' => array('csfr'),
    'functions' => array('configure')
);

$ViewList['deletepc'] = array(
    'params' => array('index','id'),
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

$FunctionList['use'] = array('explain' => 'Allow operator to use Elastic Search module');
$FunctionList['configure'] = array('explain' => 'Allow operator to configure Elastic Search module');