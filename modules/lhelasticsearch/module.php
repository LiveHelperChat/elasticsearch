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
    'functions' => array('use')
);

$ViewList['list'] = array(
    'params' => array(),
    'uparams' => array('timefrom_type','ipp','ip','theme_ids','expression','fuzzy_prefix','fuzzy','iwh_ids','timefromts','transfer_happened','not_invitation','proactive_chat','phone','chat_status_ids','hof','hvf','referrer','session_referrer','export','view','dropped_chat','abandoned_chat','has_unread_op_messages','cls_us','subject_id','bot_ids','has_operator','with_bot','without_bot','country_ids','attr_int_1_multi','attr_int_2_multi','attr_int_3_multi','attr_int_4_multi','no_user','uagent','exact_match','ds','department_ids','department_group_ids','user_ids','group_ids','invitation_id','attr_int_1','attr_int_2','attr_int_3','attr_int_4','attr_int_5','attr_int_6','attr_int_7','attr_int_8','attr_int_9','attr_int_10','attr_int_11','attr_int_12','attr_int_13','attr_int_14','attr_int_15','search_in','sort_chat','keyword','tab','chat_id','message_text','nick','sort_msg','email','user_id','department_id','timefrom','timefrom_minutes','timefrom_hours','timefrom_seconds','timeto','timeto_minutes','timeto_hours','timeto_seconds','department_group_id','group_id','region'),
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
        'frt_from',
        'frt_till',
        'mart_from',
        'mart_till',
        'aart_till',
        'aart_from',
        'theme_ids',
        'priority_from',
        'priority_till'
    ),
    'functions' => array('use')
);

$ViewList['expressiongenerator'] = array(
    'params' => array('scope'),
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
        'timefrom', 'timefrom_minutes','timefrom_hours','timefrom_seconds',
        'timeto','timeto_minutes', 'timeto_hours','timeto_seconds',
        'department_group_id','group_id', 'user_ids','no_user','has_operator',
        'export','view','subject_id', 'department_ids',
        'department_group_ids','group_ids','hvf','response_type','status','status_conv',
        'sender_host','from_host','sender_address','is_followup','undelivered','lang_ids',
        'phone','opened','search_email_in','timefromts','fuzzy','fuzzy_prefix','status_conv_id',
        'status_msg_id','mailbox_ids','expression','ipp','has_attachment','is_external','ids','timefrom_type',
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
        'mailbox_ids',
        'ids'
    ),
    'functions' => array('use')
);

$ViewList['raw'] = array(
    'params' => array('index','id'),
    'functions' => array('use')
);

$ViewList['listov'] = array(
    'params' => array(),
    'functions' => array('use')
);

$ViewList['rawov'] = array(
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

$ViewList['statisticpending'] = array(
    'params' => array(),
    'uparams' => array(
        0 => 'timeto_include_hours',
        1 => 'timefrom_include_hours',
        2 => 'invitation_id',
        3 => 'group_chart_type',
        4 => 'group_limit',
        5 => 'chart_type',
        6 => 'group_field',
        7 => 'groupby',
        8 => 'export',
        9 => 'report',
        10 => 'transfer_happened',
        11 => 'invitation_ids',
        12 => 'wait_time_till',
        13 => 'wait_time_from',
        14 => 'subject_ids',
        15 => 'department_ids',
        16 => 'department_group_ids',
        17 => 'group_ids',
        18 => 'user_ids',
        19 => 'timeintervalto_hours',
        20 => 'timeintervalfrom_hours',
        21 => 'group_by',
        22 => 'xls',
        23 => 'tab',
        24 => 'timefrom',
        25 => 'timeto',
        26 => 'department_id',
        27 => 'user_id',
        28 => 'group_id',
        29 => 'department_group_id',
        30 => 'timefrom_seconds',
        31 => 'timefrom_minutes',
        32 => 'timefrom_hours',
        33 => 'timeto_hours',
        34 => 'timeto_minutes',
        35 => 'timeto_seconds',
        36 => 'exclude_offline',
        37 => 'with_bot',
        38 => 'dropped_chat',
        39 => 'online_offline',
        40 => 'without_bot',
        41 => 'proactive_chat',
        42 => 'no_operator',
        43 => 'has_unread_messages',
        44 => 'not_invitation',
        45 => 'has_operator',
        46 => 'abandoned_chat',
        47 => 'bot_ids',
        48 => 'cls_us',
        49 => 'has_unread_op_messages',
        51 => 'opened',
        52 => 'country_ids',
        53 => 'region',
        54 => 'frt_from',
        55 => 'frt_till',
        56 => 'mart_from',
        57 => 'mart_till',
        58 => 'aart_till',
        59 => 'aart_from',
        60 => 'reporthash',
        61 => 'reportts',
        62 => 'reportverified',
        63 => 'r',
        64 => 'is_external',
        65 => 'has_attachment',
        66 => 'has_online_hours',
        67 => 'exclude_deactivated',
        68 => 'mail_conv_user'
     ),
    'functions' => array('use'),
    'multiple_arguments' => array('bot_ids','subject_ids','department_ids','group_ids','user_ids','department_group_ids','invitation_ids','chart_type','country_ids')
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

$ViewList['forcemerge'] = array(
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

$ViewList['deleteov'] = array(
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

$ViewList['reindex'] = array(
    'params' => array(),
    'uparams' => array(),
    'functions' => array('configure')
);

$FunctionList['use'] = array('explain' => 'Allow operator to use Elastic Search module');
$FunctionList['delete'] = array('explain' => 'Allow operator to delete permanently mail records');
$FunctionList['configure'] = array('explain' => 'Allow operator to configure Elastic Search module');