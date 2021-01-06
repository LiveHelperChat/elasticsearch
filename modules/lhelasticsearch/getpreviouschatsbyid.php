<?php

$esOptions = erLhcoreClassModelChatConfig::fetch('elasticsearch_options');
$dataOptions = (array)$esOptions->data;

if (isset($dataOptions['disable_es']) && $dataOptions['disable_es'] == 1) {
    echo json_encode(array('result' => ''));
    exit;
}

if (!empty($Params['user_parameters_unordered']['type']) && $Params['user_parameters_unordered']['type'] == 'ou') {
    $online_user = erLhcoreClassModelChatOnlineUser::fetch($Params['user_parameters']['chat_id']);
    $chat = new erLhcoreClassModelChat();
    $chat->online_user_id = $online_user->id;
} else {
    $chat = erLhcoreClassModelChat::fetch($Params['user_parameters']['chat_id']);
    $online_user = $chat->online_user;
}

$tpl = erLhcoreClassTemplate::getInstance('elasticsearch/getpreviouschats.tpl.php');

if ($online_user !== false) {

    $sparams['body']['query']['bool']['must'][]['term']['online_user_id'] = $online_user->id;

    $dateIndex = array('date_index' => array('gte' => time()-6*31*24*3600));

    erLhcoreClassChatEventDispatcher::getInstance()->dispatch('elasticsearch.getpreviouschats', array(
        'chat' => $chat,
        'sparams' => & $sparams,
        'date_index' => & $dateIndex
    ));

    $previousChats = erLhcoreClassModelESChat::getList(array(
        'offset' => 0,
        'limit' => 50,
        'body' => array_merge(array(
            'sort' => array(
                'time' => array(
                    'order' => 'desc'
                )
            )
        ), $sparams['body'])
    ),
    $dateIndex);

    $chatIds = array();

    foreach ($previousChats as $prevChat) {
        $chatIds[$prevChat->chat_id] = array();
    }

    erLhcoreClassChatArcive::setArchiveAttribute($chatIds);

    $tpl->set('chatsPrevArchives', $chatIds);

    $tpl->set('chatsPrev', $previousChats);

    $tpl->set('chat', $chat);

    echo json_encode(array('result' => $tpl->fetch()));
}
exit;

?>