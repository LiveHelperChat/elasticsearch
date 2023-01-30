<?php

// php cron.php -s site_admin -e elasticsearch -c cron/sync_mail -p 1635496470_1675071319_1
// php cron.php -s site_admin -e elasticsearch -c cron/sync_mail -p <unix_timestamp_start>_<unix_timestamp_end>_<0 - dry run|1 - execute>

$sort = array('conversation_id' => array('order' => 'desc'));

$params = explode('_', $cronjobPathOption->value);

echo "Start - ",date('Y-m-d H:i:s',$params[0]);
echo "End - ",date('Y-m-d H:i:s',$params[1]);

$sparams['body']['query']['bool']['must'][]['range']['time']['gte'] = $params[0] * 1000;
$dateFilter['gte'] = $params[0];

$sparams['body']['query']['bool']['must'][]['range']['time']['lte'] = $params[1] * 1000;
$dateFilter['lte'] = $params[1];

$hasEmails = true;
$counterOffset = 0;

while ($hasEmails) {

    $chats = erLhcoreClassModelESMail::getList(array(
        'offset' => $counterOffset * 100,
        'limit' => 100,
        'body' => array_merge(array(
            'sort' => $sort
        ), $sparams['body'])
    ),
    array('date_index' => $dateFilter));

    if (count($chats) > 0) {
        foreach ($chats as $esmail) {
            if (erLhcoreClassModelMailconvConversation::getCount(['filter' => ['id' => $esmail->conversation_id]]) == 0) {
                if (isset($params[2]) && $params[2] == 1) {
                    echo "Removing - ",$esmail->conversation_id,"\n";
                    $esmail->removeThis();
                } else {
                    echo "Dry run, missing e-mail - ",$esmail->conversation_id,"\n";
                }
            }
            echo ($esmail->ctime/1000),"-",$esmail->conversation_id,"\n";
        }
    } else {
        $hasEmails = false;
    }

    $counterOffset++;
}
