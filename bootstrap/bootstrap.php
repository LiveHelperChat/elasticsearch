<?php
#[\AllowDynamicProperties]
class erLhcoreClassExtensionElasticsearch
{
    public function __construct()
    {}

    public function run()
    {
        $this->registerAutoload();
    }
    
    public function registerAutoload()
    {
    	$dispatcher = erLhcoreClassChatEventDispatcher::getInstance();

        $dispatcher->listen('chat.close', '\LiveHelperChatExtension\elasticsearch\providers\Index\Scheduler::indexChatDelay');
        $dispatcher->listen('chat.modified', '\LiveHelperChatExtension\elasticsearch\providers\Index\Scheduler::indexChatModify');
        $dispatcher->listen('chat.subject_remove', '\LiveHelperChatExtension\elasticsearch\providers\Index\Scheduler::indexChatModify');
        $dispatcher->listen('chat.subject_add', '\LiveHelperChatExtension\elasticsearch\providers\Index\Scheduler::indexChatModify');
        $dispatcher->listen('chat.msg_removed', '\LiveHelperChatExtension\elasticsearch\providers\Index\Scheduler::indexChatModify');

        if (!(isset($this->settings_personal['disable_es']) && $this->settings_personal['disable_es'] == 1)) {

            $dispatcher->listen('chat.delete', 'erLhcoreClassElasticSearchIndex::indexChatDelete');
            $dispatcher->listen('chat.workflow.has_previous_messages', 'erLhcoreClassElasticSearchIndex::hasPreviousMessages');
            $dispatcher->listen('chat.workflow.get_chat_history', 'erLhcoreClassElasticSearchIndex::getChatHistory');
            $dispatcher->listen('chat.chathistory', 'erLhcoreClassElasticSearchIndex::getConcurrentChats');

            $dispatcher->listen('statistic.valid_tabs', 'erLhcoreClassElasticSearchStatistic::appendStatisticTab');
            $dispatcher->listen('statistic.process_tab', 'erLhcoreClassElasticSearchStatistic::processTab');

            $dispatcher->listen('restapi.swagger', 'LiveHelperChatExtension\elasticsearch\providers\Swagger\Definition::setDefinition');

            if (isset($this->settings_personal['use_es_statistic']) && $this->settings_personal['use_es_statistic'] == true)
            {
                $dispatcher->listen('statistic.gettopchatsbycountry', 'erLhcoreClassElasticSearchStatistic::statisticGettopchatsbycountry');
                $dispatcher->listen('statistic.numberofchatsdialogsbyuserparticipant', 'erLhcoreClassElasticSearchStatistic::numberOfChatsDialogsByUserParticipant');
                $dispatcher->listen('statistic.numberofchatsdialogsbyuser', 'erLhcoreClassElasticSearchStatistic::statisticNumberofchatsdialogsbyuser');
                $dispatcher->listen('statistic.numberofchatsdialogsbydepartment', 'erLhcoreClassElasticSearchStatistic::statisticNumberofchatsdialogsbydepartment');
                $dispatcher->listen('statistic.avgwaittimeuser', 'erLhcoreClassElasticSearchStatistic::statisticAvgwaittimeuser');
                $dispatcher->listen('statistic.averageofchatsdialogsbyuser', 'erLhcoreClassElasticSearchStatistic::statisticAverageofchatsdialogsbyuser');
                $dispatcher->listen('statistic.getnumberofchatspermonth', 'erLhcoreClassElasticSearchStatistic::statisticGetnumberofchatspermonth');
                $dispatcher->listen('statistic.getnumberofchatswaittime', 'erLhcoreClassElasticSearchStatistic::statisticGetnumberofchatswaittime');
                $dispatcher->listen('statistic.getworkloadstatistic', 'erLhcoreClassElasticSearchStatistic::statisticGetworkloadstatistic');
                $dispatcher->listen('statistic.getaveragechatduration', 'erLhcoreClassElasticSearchStatistic::statisticGetaveragechatduration');
                $dispatcher->listen('statistic.numberofmessagesbyuser', 'erLhcoreClassElasticSearchStatistic::statisticNumberofmessagesbyuser');
                $dispatcher->listen('statistic.getnumberofchatsperday', 'erLhcoreClassElasticSearchStatistic::statisticGetnumberofchatsperday');
                $dispatcher->listen('statistic.getnumberofchatsperweekday', 'erLhcoreClassElasticSearchStatistic::statisticGetnumberofchatsperweekday');
                $dispatcher->listen('statistic.getnumberofchatsperweek', 'erLhcoreClassElasticSearchStatistic::statisticgetNumberOfChatsPerWeek');
                $dispatcher->listen('statistic.getnumberofchatswaittimeperweekday', 'erLhcoreClassElasticSearchStatistic::getNumberOfChatsWaitTimePerWeekDay');
                $dispatcher->listen('statistic.getnumberofchatswaittimeperday', 'erLhcoreClassElasticSearchStatistic::statisticGetnumberofchatswaittimeperday');
                $dispatcher->listen('statistic.getnumberofchatswaittimeperweek', 'erLhcoreClassElasticSearchStatistic::statisticGetNumberOfChatsWaitTimePerWeek');

                $dispatcher->listen('statistic.getlast24hstatistic', 'erLhcoreClassElasticSearchStatistic::statisticGetlast24hstatistic');
                $dispatcher->listen('statistic.gettoptodaysoperators', 'erLhcoreClassElasticSearchStatistic::statisticGettoptodaysoperators');
                $dispatcher->listen('statistic.getagentstatistic', 'erLhcoreClassElasticSearchStatistic::statisticGetagentstatistic');
                $dispatcher->listen('statistic.getperformancestatistic', 'erLhcoreClassElasticSearchStatistic::statisticGetperformancestatistic');
                $dispatcher->listen('statistic.getsubjectsstatistic', 'erLhcoreClassElasticSearchStatistic::statisticsubjectsStatistic');
                $dispatcher->listen('statistic.getratingbyuser', 'erLhcoreClassElasticSearchStatistic::statisticGetratingbyuser');
                $dispatcher->listen('statistic.chatsstatistic_filter', 'erLhcoreClassElasticSearchStatistic::statisticFilter');
                $dispatcher->listen('statistic.active_filter', 'erLhcoreClassElasticSearchStatistic::statisticFilter');

                // Grouping charts by field and date
                $dispatcher->listen('statistic.nickgroupingdatenick', 'erLhcoreClassElasticSearchStatistic::nickGroupingDateNick');
                $dispatcher->listen('statistic.nickgroupingdatenickday', 'erLhcoreClassElasticSearchStatistic::nickGroupingDateNickDay');
                $dispatcher->listen('statistic.nickgroupingdatenickweekday', 'erLhcoreClassElasticSearchStatistic::nickGroupingDateNickWeekDay');
                $dispatcher->listen('statistic.nickgroupingdatenickweek', 'erLhcoreClassElasticSearchStatistic::nickGroupingDateNickWeek');

                // Grouping charts by field
                $dispatcher->listen('statistic.nickgroupingdate', 'erLhcoreClassElasticSearchStatistic::nickGroupingDate');
                $dispatcher->listen('statistic.nickgroupingdateweekday', 'erLhcoreClassElasticSearchStatistic::nickGroupingDateWeekDay');
                $dispatcher->listen('statistic.nickgroupingdateday', 'erLhcoreClassElasticSearchStatistic::nickGroupingDateDay');
                $dispatcher->listen('statistic.nickgroupingdateweek', 'erLhcoreClassElasticSearchStatistic::nickGroupingDateWeek');

                // Total chats by channel
                $dispatcher->listen('statistic.getnumberofchatsperchannel', 'erLhcoreClassElasticSearchStatistic::byChannel');

                // Views
                $dispatcher->listen('views.loadview', 'erLhcoreClassElasticSearchView::loadView');
                $dispatcher->listen('views.editview', 'erLhcoreClassElasticSearchView::editView');
                $dispatcher->listen('views.update_vew', 'erLhcoreClassElasticSearchView::updateView');
                $dispatcher->listen('views.export', 'erLhcoreClassElasticSearchView::exportView');

                // Mail module

                // Statistic
                $dispatcher->listen('mail.statistic.messagesperinterval', 'erLhcoreClassElasticSearchStatistic::mailMessagesperinterval');
                $dispatcher->listen('mail.statistic.messagesperuser', 'erLhcoreClassElasticSearchStatistic::mailMessagesperinterval');
                $dispatcher->listen('mail.statistic.messagesperdep', 'erLhcoreClassElasticSearchStatistic::mailMessagesperinterval');
                $dispatcher->listen('mail.statistic.avginteractionperdep', 'erLhcoreClassElasticSearchStatistic::mailMessagesperinterval');
                $dispatcher->listen('mail.statistic.avginteractionperuser', 'erLhcoreClassElasticSearchStatistic::mailMessagesperinterval');
                $dispatcher->listen('mail.statistic.messagesperhour', 'erLhcoreClassElasticSearchStatistic::mailMessagesperinterval');
                $dispatcher->listen('mail.statistic.attrbyperinterval', 'erLhcoreClassElasticSearchStatistic::mailMessagesperinterval');

                // Conversations
                $dispatcher->listen('mail.conversation.after_save', '\LiveHelperChatExtension\elasticsearch\providers\Index\Scheduler::conversationIndex');
                $dispatcher->listen('mail.conversation.after_update', '\LiveHelperChatExtension\elasticsearch\providers\Index\Scheduler::conversationIndex');

                // Messages
                $dispatcher->listen('mail.message.after_save', '\LiveHelperChatExtension\elasticsearch\providers\Index\Scheduler::mailMessageIndex');
                $dispatcher->listen('mail.message.after_update', '\LiveHelperChatExtension\elasticsearch\providers\Index\Scheduler::mailMessageIndex');
                $dispatcher->listen('mail.message.after_remove', '\LiveHelperChatExtension\elasticsearch\providers\Index\Scheduler::mailMessageRemove');
                $dispatcher->listen('mail.subject_remove', '\LiveHelperChatExtension\elasticsearch\providers\Index\Scheduler::mailMessageIndex');
                $dispatcher->listen('mail.subject_add', '\LiveHelperChatExtension\elasticsearch\providers\Index\Scheduler::mailMessageIndex');

                // Online visitors
                if (isset($this->settings_personal['use_es_ov']) && $this->settings_personal['use_es_ov'] == true) {
                    $dispatcher->listen('chat.online_user.after_save', '\LiveHelperChatExtension\elasticsearch\providers\Index\Scheduler::onlineVisitorIndex');
                    $dispatcher->listen('chat.online_user.after_update', '\LiveHelperChatExtension\elasticsearch\providers\Index\Scheduler::onlineVisitorIndex');
                    $dispatcher->listen('chat.online_user.after_remove', '\LiveHelperChatExtension\elasticsearch\providers\Index\OnlineVisitor::removeOnlineVisitor');
                    $dispatcher->listen('chat.online_users_get_list', '\LiveHelperChatExtension\elasticsearch\providers\Index\OnlineVisitor::getOnlineVisitors');
                }

                // Custom unordered parameters support
                $dispatcher->listen('statistic.uparams_append', 'erLhcoreClassElasticSearchStatistic::uparamsAppend');
            }
        }

        spl_autoload_register(array(
            $this,
            'autoload'
        ), true, false);
    }
    
    public function autoload($className)
    {
        $classesArray = array(
            'erLhcoreClassElasticSearchUpdate'   => 'extension/elasticsearch/classes/lhelasticsearchupdate.php',
            'erLhcoreClassElasticClient'         => 'extension/elasticsearch/classes/lhelasticsearchclient.php',
            'erLhcoreClassElasticTrait'          => 'extension/elasticsearch/classes/lhelastictrait.php',
            'erLhcoreClassElasticSearchStatistic'=> 'extension/elasticsearch/classes/lhelasticsearchstatistic.php',
            'erLhcoreClassModelESChat'           => 'extension/elasticsearch/classes/erlhcoreclassmodeleschat.php',
            'erLhcoreClassModelESMsg'            => 'extension/elasticsearch/classes/erlhcoreclassmodelesmsg.php',
            'erLhcoreClassModelESParticipant'    => 'extension/elasticsearch/classes/erlhcoreclassmodelesparticipant.php',
            'erLhcoreClassModelESOnlineSession'  => 'extension/elasticsearch/classes/erlhcoreclassmodelesonlinesession.php',
            'erLhcoreClassModelESPendingChat'    => 'extension/elasticsearch/classes/erlhcoreclassmodelespendingchat.php',
            'erLhcoreClassModelESOnlineOperator' => 'extension/elasticsearch/classes/erlhcoreclassmodelesonlineoperator.php',
            'erLhcoreClassElasticSearchIndex'    => 'extension/elasticsearch/classes/lhelasticsearchindex.php',
            'erLhcoreClassElasticSearchWorker'   => 'extension/elasticsearch/classes/lhqueueelasticsearchworker.php',
            'erLhcoreClassElasticSearchView'     => 'extension/elasticsearch/classes/lhelasticsearchview.php',
            'erLhcoreClassModelESMail'           => 'extension/elasticsearch/classes/erlhcoreclassmodelesmail.php',
            'erLhcoreClassModelESMsgAgg'         => 'extension/elasticsearch/classes/erlhcoreclassmodelesmsgagg.php'
        );

        if (key_exists($className, $classesArray)) {
            include_once $classesArray[$className];
        }
    }
   
    public function __get($var)
    {
        switch ($var) {

            case 'settings':
                    $this->settings = include ('extension/elasticsearch/settings/settings.ini.php');
                    return $this->settings;
                break;

            case 'settings_personal':
                    $esOptions = erLhcoreClassModelChatConfig::fetch('elasticsearch_options');
                    $this->settings_personal = (array)$esOptions->data;
                return $this->settings_personal;
                break;

            default:
                ;
                break;
        }
    }

    public static function getSession() {
        if (! isset ( self::$persistentSession )) {
            self::$persistentSession = new ezcPersistentSession ( ezcDbInstance::get (), new ezcPersistentCodeManager ( './extension/elasticsearch/pos' ) );
        }
        return self::$persistentSession;
    }

    private static $persistentSession;

}


