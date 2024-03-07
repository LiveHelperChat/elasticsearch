<?php
/*
 * php cron.php -s site_admin -e elasticsearch -c cron/mail/delete_mail
 *
 * Deletes mails in the background based on filter setup. In this case it just schedules records to be deleted.
 * To finish deletion process you have to run php cron.php -s site_admin -e elasticsearch -c cron/mail/delete_mail_item
 * */
$fp = fopen("cache/cron_mail_delete_mail_elastic.lock", "w+");

// Gain the lock
if (!flock($fp, LOCK_EX | LOCK_NB)) {
    echo "Couldn't get the lock! Another process is already running\n";
    fclose($fp);
    exit;
} else {
    echo "Lock acquired. Starting process!\n";
}

foreach (\LiveHelperChatExtension\elasticsearch\providers\Delete\DeleteFilter::getList([
    'filterin' => ['status' => [
        \LiveHelperChatExtension\elasticsearch\providers\Delete\DeleteFilter::STATUS_PENDING,
        \LiveHelperChatExtension\elasticsearch\providers\Delete\DeleteFilter::STATUS_IN_PROGRESS,
    ]]
]) as $filterData) {

    // Lock filter object
    $db = ezcDbInstance::get();
    $db->beginTransaction();
        $filterData->syncAndLock();
        if ($filterData->status == \LiveHelperChatExtension\elasticsearch\providers\Delete\DeleteFilter::STATUS_PENDING || ($filterData->started_at < (time() - 1800) && $filterData->status == \LiveHelperChatExtension\elasticsearch\providers\Delete\DeleteFilter::STATUS_IN_PROGRESS)) {
            $filterData->status = \LiveHelperChatExtension\elasticsearch\providers\Delete\DeleteFilter::STATUS_IN_PROGRESS;
            $filterData->started_at = time();
        } else {
            $db->commit();
            continue;
        }
        $filterData->updateThis(['update' => ['status','started_at']]);
    $db->commit();

    $filterDataParams = json_decode($filterData->filter,true);
    $filterParams = $filterDataParams['filter'];
    $filterDateIndex = $filterDataParams['date_index'];
    $has_items = true;

    // Schedule records for deletion
    for ($i = 1; $i < 200; $i++) {
       
        $filterParamsBatch = $filterParams;

        $filterParamsBatch['sort']['conversation_id']['order'] = 'asc';
        $filterParamsBatch['query']['bool']['must'][] = ['range' => ['conversation_id' => ['gt' => $filterData->last_id]]];

        $itemsToDelete = erLhcoreClassModelESMail::getList(array(
            'offset' => 0,
            'limit' => 40,
            'body' => $filterParamsBatch
        ),array('date_index' => $filterDateIndex));

        if (empty($itemsToDelete)) {
            $filterData->status = \LiveHelperChatExtension\elasticsearch\providers\Delete\DeleteFilter::STATUS_FINISHED;
            $has_items = false;
            echo "Finished items - ",$filterData->id,"\n";
            break;
        } else {
            foreach ($itemsToDelete as $item) {
                $deleteItem = new \LiveHelperChatExtension\elasticsearch\providers\Delete\DeleteItem();
                $deleteItem->filter_id = $filterData->id;
                $deleteItem->conversation_id = $item->id;
                $deleteItem->index = $item->meta_data['index'];
                $deleteItem->saveThis();
                $filterData->last_id = $item->conversation_id;
            }
            $filterData->updateThis(['update' => ['last_id']]);
        }
    }

    if ($has_items == true) {
        $filterData->status = \LiveHelperChatExtension\elasticsearch\providers\Delete\DeleteFilter::STATUS_PENDING;
    }

    $filterData->finished_at = time();
    $filterData->updateThis(['update' => ['status','last_id','finished_at']]);
}

flock($fp, LOCK_UN); // release the lock
fclose($fp);

?>
