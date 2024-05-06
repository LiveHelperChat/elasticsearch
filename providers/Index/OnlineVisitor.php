<?php

namespace LiveHelperChatExtension\elasticsearch\providers\Index;

#[\AllowDynamicProperties]
class OnlineVisitor
{
    use \erLhcoreClassElasticTrait;

    public function getState()
    {
        return array(
            'id' => $this->id,
            'ip' => $this->ip,
            'vid' => $this->vid,
            'current_page' => $this->current_page,
            'invitation_seen_count' => $this->invitation_seen_count,
            'page_title' => $this->page_title,
            'chat_id' => $this->chat_id, // For future
            'chat_time' => $this->chat_time,
            'last_visit_prev' => $this->last_visit_prev,
            'last_visit' => $this->last_visit,
            'first_visit' => $this->first_visit,
            'user_agent' => $this->user_agent,
            'user_country_name' => $this->user_country_name,
            'user_country_code' => $this->user_country_code,
            'operator_message' => $this->operator_message,
            'operator_user_id' => $this->operator_user_id,
            'operator_user_proactive' => $this->operator_user_proactive,
            'message_seen' => $this->message_seen,
            'message_seen_ts' => $this->message_seen_ts,
            'pages_count' => $this->pages_count,
            'tt_pages_count' => $this->tt_pages_count,
            'location' => $this->location,
            'city' => $this->city,
            'identifier' => $this->identifier,
            'time_on_site' => $this->time_on_site,
            'tt_time_on_site' => $this->tt_time_on_site,
            'referrer' => $this->referrer,
            'invitation_id' => $this->invitation_id,
            'total_visits' => $this->total_visits,
            'invitation_count' => $this->invitation_count,
            'requires_email' => $this->requires_email,
            'requires_username' => $this->requires_username,
            'requires_phone' => $this->requires_phone,
            'dep_id' => $this->dep_id,
            'conversion_id' => $this->conversion_id,
            'reopen_chat' => $this->reopen_chat,
            'operation' => $this->operation,
            'operation_chat' => $this->operation_chat,
            'screenshot_id' => $this->screenshot_id,
            'online_attr' => $this->online_attr,
            'online_attr_system' => $this->online_attr_system,
            'online_attr_system_flat' => $this->online_attr_system_flat,
            'visitor_tz' => $this->visitor_tz,
            'last_check_time' => $this->last_check_time,
            'user_active' => $this->user_active,
            'notes' => $this->notes,
            'device_type' => $this->device_type,
        );
    }

    public function __get($var)
    {
        if ($this->online_visitor == null) {
            $this->online_visitor = new \erLhcoreClassModelChatOnlineUser();

            $attributes = $this->getState();

            foreach (['chat_time','last_visit_prev','last_visit','first_visit','message_seen_ts','last_check_time'] as $attr) {
                $attributes[$attr] = $attributes[$attr] > 0 ? round($attributes[$attr] / 1000) : 0;
            }

            $this->online_visitor->setState($attributes);
        }

        if (in_array($var,['lat','lon']) && is_array($this->location)) {
            $this->lat = $this->location[1];
            $this->lon = $this->location[0];
            return $this->{$var};
        }

        $this->{$var} = $this->online_visitor->{$var};

        return $this->{$var};
    }

    public static function removeOnlineVisitor($params)
    {
        self::getSession();
        $onlineUser = self::fetch($params['online_user']->id, self::$indexName . '-' . self::$elasticType);
        if (is_object($onlineUser)) {
            $onlineUser->removeThis();
        }
    }

    public static function getOnlineVisitors($params)
    {
        self::getSession();

        $sparams = array();
        $sparams['index'] = \erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionElasticsearch')->settings['index_search'] . '-' . self::$elasticType;
        $sparams['ignore_unavailable'] = true;
        $sparams['body'] = [];

        \erLhcoreClassElasticSearchStatistic::formatFilter($params['filter'], $sparams);

        if (isset($params['attr_filter']) && !empty($params['attr_filter'])) {
            foreach ($params['attr_filter'] as $field => $values) {
               $sparams['body']['query']['bool']['must'][] = ["nested"  => [
                   "path" => "online_attr_system_flat",
                   "query" => [
                       "terms" => [
                           "online_attr_system_flat." . $field => $values
                        ]
                   ]
               ]];
            }
        }
        
        return array(
            'status' => \erLhcoreClassChatEventDispatcher::STOP_WORKFLOW,
            'list' => self::getList(
                array(
                    'offset' => $params['filter']['offset'],
                    'limit' => $params['filter']['limit'],
                    'body' => array_merge(array(
                        'sort' => array(
                            'last_visit' => array(
                                'order' => 'desc'
                            )
                        )
                    ), $sparams['body'])
                )
            )
        );
    }

    public static $elasticType = 'lh_online_visitor';

    public $id = null;
    public $ip = null;
    public $vid = null;
    public $current_page = null;
    public $invitation_seen_count = null;
    public $page_title = null;
    public $chat_id = null;
    public $chat_time = null;
    public $last_visit_prev = null;
    public $last_visit = null;
    public $first_visit= null;
    public $user_agent = null;
    public $user_country_name = null;
    public $user_country_code = null;
    public $operator_message = null;
    public $operator_user_id = null;
    public $operator_user_proactive = null;
    public $message_seen = null;
    public $message_seen_ts = null;
    public $pages_count = null;
    public $tt_pages_count = null;
    public $location = null;
    public $city = null;
    public $identifier = null;
    public $time_on_site = null;
    public $tt_time_on_site = null;
    public $referrer = null;
    public $invitation_id = null;
    public $total_visits = null;
    public $invitation_count = null;
    public $requires_email = null;
    public $requires_username = null;
    public $requires_phone = null;
    public $dep_id = null;
    public $conversion_id = null;
    public $reopen_chat = null;
    public $operation = null;
    public $operation_chat = null;
    public $screenshot_id = null;
    public $online_attr = null;
    public $online_attr_system = null;
    public $online_attr_system_flat = null;
    public $visitor_tz = null;
    public $last_check_time = null;
    public $user_active = null;
    public $notes = null;
    public $device_type = null;
    public $online_visitor = null;
    
}