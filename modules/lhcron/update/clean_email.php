<?php
// /usr/bin/php cron.php -s site_admin -e elasticsearch -c cron/update/clean_email -p 1722512526000

erLhcoreClassModelESMail::getList();

$dateFilter = [];
$dateFilter['gte'] = mktime(0,0,0,1,1,2017);
$dateFilter['lte'] = mktime(0,0,0,12,31,date('Y'));

if (is_numeric($cronjobPathOption->value)) {
    $previousConversation = $conversationId = (int)$cronjobPathOption->value;
} else {
    $previousConversation = $conversationId = 0;
}

function cleanEmail($email) {
    $atPos = strrpos($email, "@");
    $name =  str_replace('.','',substr($email, 0, $atPos));
    $domain = substr($email, $atPos);
    return strtolower($name . $domain);
}

while (true) {
    $sparams = [];
    $sparams['body']['query']['bool']['must'][]['range']['ctime']['lte'] = $conversationId;
    $chats = erLhcoreClassModelESMail::getList(array(
        'offset' => 0,
        'limit' => 500,
        'body' => array_merge(array(
            'sort' => ['ctime' => ['order' => 'desc']]
        ), $sparams['body'])
    ),
    array('date_index' => $dateFilter));

    foreach ($chats as $msg) {

        if ($msg->from_address == '' && $msg->customer_address == '') {
            continue;
        }

        if ($msg->from_address != '') {
            $msg->from_address_clean = cleanEmail($msg->from_address);
        }

        if ($msg->customer_address != '') {
            $msg->customer_address_clean = cleanEmail($msg->customer_address);
        }

        $msg->saveThis();

        $conversationId = $msg->ctime;
    }

    if ($previousConversation == $conversationId) {
        echo "Finished\n";
        exit;
    } else {
        $previousConversation = $conversationId;
        echo "Last update batch - ",$conversationId,"--",count($chats),"\n";
    }
}




?>