<?php

namespace LiveHelperChatExtension\elasticsearch\providers\Index;

class Scheduler
{
    public static function onlineVisitorIndex($params)
    {

        $db = \ezcDbInstance::get();
        $stmt = $db->prepare('INSERT IGNORE INTO lhc_lhesou_index (`online_user_id`) VALUES (:online_user_id)');
        $stmt->bindValue(':online_user_id', $params['online_user']->id, \PDO::PARAM_STR);
        $stmt->execute();

        $randomPropability = isset(\erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionElasticsearch')->settings_personal['random_ov']) ? \erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionElasticsearch')->settings_personal['random_ov'] : 1;

        // Schedule background worker for instant indexing
        if (rand(1,$randomPropability) == 1 && class_exists('erLhcoreClassExtensionLhcphpresque')) {
            \erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionLhcphpresque')->enqueue('lhc_elastic_queue', 'erLhcoreClassElasticSearchWorker', array());
        }
    }

    public static function mailMessageRemove($params) {
        $db = \ezcDbInstance::get();
        $stmt = $db->prepare('INSERT IGNORE INTO lhc_lhesmail_index (`mail_id`,`op`,`udate`) VALUES (:mail_id,3,:udate)');
        $stmt->bindValue(':mail_id', $params['message']->id, \PDO::PARAM_STR);
        $stmt->bindValue(':udate', $params['message']->udate, \PDO::PARAM_STR);
        $stmt->execute();

        // Schedule background worker for instant indexing
        if (class_exists('erLhcoreClassExtensionLhcphpresque')) {
            \erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionLhcphpresque')->enqueue('lhc_elastic_queue', 'erLhcoreClassElasticSearchWorker', array());
        }
    }

    public static function mailMessageIndex($params) {
        $db = \ezcDbInstance::get();

        try {
            $stmt = $db->prepare('INSERT IGNORE INTO lhc_lhesmail_index (`mail_id`) VALUES (:mail_id)');
            $stmt->bindValue(':mail_id', $params['message']->id, \PDO::PARAM_INT);
            $stmt->execute();
        } catch (\Exception $e) {
            // Ignore an error if deadlock is found
            // Perhaps we should handle it different way, but usually it happens rarely
            // and mails re re-indexed multiple times during their lifespan
        }

        // Schedule background worker for instant indexing
        if (class_exists('erLhcoreClassExtensionLhcphpresque')) {
            \erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionLhcphpresque')->enqueue('lhc_elastic_queue', 'erLhcoreClassElasticSearchWorker', array());
        }
    }

    public static function conversationIndex($params) {
        $db = \ezcDbInstance::get();
        try {
            $stmt = $db->prepare('INSERT IGNORE INTO lhc_lhesmail_index (`mail_id`,`op`) VALUES (:mail_id,1)');
            $stmt->bindValue(':mail_id', $params['conversation']->id, \PDO::PARAM_INT);
            $stmt->execute();
        } catch (\Exception $e) {
            // Ignore an error if deadlock is found
            // Perhaps we should handle it different way, but usually it happens rarely
            // and mails re re-indexed multiple times during their lifespan
        }

        // Schedule background worker for instant indexing
        if (class_exists('erLhcoreClassExtensionLhcphpresque')) {
            \erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionLhcphpresque')->enqueue('lhc_elastic_queue', 'erLhcoreClassElasticSearchWorker', array());
        }
    }

    public static function indexChatDelay($params)
    {
        $esOptions = \erLhcoreClassModelChatConfig::fetch('elasticsearch_options');
        $dataOptions = (array)$esOptions->data;

        $db = \ezcDbInstance::get();
        $stmt = $db->prepare('INSERT IGNORE INTO lhc_lheschat_index (`chat_id`) VALUES (:chat_id)');
        $stmt->bindValue(':chat_id', $params['chat']->id, \PDO::PARAM_STR);
        $stmt->execute();

        // Schedule background worker for instant indexing
        if (isset($dataOptions['use_php_resque']) && $dataOptions['use_php_resque'] == 1) {
            \erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionLhcphpresque')->enqueue('lhc_elastic_queue', 'erLhcoreClassElasticSearchWorker', array());
        }
    }

    public static function indexChatModify($params)
    {
        if ($params['chat']->status == \erLhcoreClassModelChat::STATUS_CLOSED_CHAT) {
            self::indexChatDelay($params);
        }
    }
}

?>