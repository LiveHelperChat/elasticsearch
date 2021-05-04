<?php

// Run every 8 hours at 36 minute of a hour
// 36 */8 * * * cd /home/www/lhc && php cron.php -s site_admin -e elasticsearch -c cron/reindex_recent > log_reindex_recent.txt /dev/null 2>&1
// /usr/bin/php cron.php -s site_admin -e elasticsearch -c cron/reindex_recent

$db = ezcDbInstance::get();
$stmt = $db->prepare('SELECT min(id) as min_id FROM lh_chat WHERE `time` > :time');
$stmt->bindValue(':time',time() - (16*3600),PDO::PARAM_INT);
$stmt->execute();
$min_id = $stmt->fetch(PDO::FETCH_COLUMN);

if (is_numeric($min_id)) {
    $stmt = $db->prepare('INSERT IGNORE INTO `lhc_lheschat_index` (`chat_id`) SELECT `lh_chat`.`id` FROM `lh_chat` WHERE `lh_chat`.`id` >= :id');
    $stmt->bindValue(':id', $min_id,PDO::PARAM_INT);
    $stmt->execute();
}

?>