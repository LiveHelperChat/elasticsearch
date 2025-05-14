<?php

namespace LiveHelperChatExtension\elasticsearch\providers\Swagger;

class RestAPIValidator
{
    public static function validateChatList()
    {
        // Chats filter
        $filterParams = \erLhcoreClassSearchHandler::getParams(array(
            'customfilterfile' => 'extension/elasticsearch/classes/filter/chat_list.php',
            'format_filter' => true,
            'use_override' => true,
            'uparams' => []
        ));

        $sparams = array(
            'body' => array()
        );

        $dateFilter = array();

        if (trim((string)$filterParams['input_form']->chat_id) != '') {
            $chat_ids = explode(',',trim($filterParams['input_form']->chat_id));
            \erLhcoreClassChat::validateFilterIn($chat_ids);

            $chat_ids = array_filter($chat_ids);

            if (!empty($chat_ids)) {
                $sparams['body']['query']['bool']['must'][]['terms']['chat_id'] = $chat_ids;
            }
        }

        if ($filterParams['input_form']->nick != '') {
            $sparams['body']['query']['bool']['must'][]['match']['nick_keyword'] = $filterParams['input_form']->nick;
        }

        if ($filterParams['input_form']->proactive_chat != '') {
            $sparams['body']['query']['bool']['must'][]['term']['chat_initiator'] = (int)$filterParams['input_form']->proactive_chat;
        }

        if ($filterParams['input_form']->not_invitation != '') {
            $sparams['body']['query']['bool']['must'][]['term']['invitation_id'] = (int)$filterParams['input_form']->not_invitation;
        }

        if ($filterParams['input_form']->ip != '') {
            $sparams['body']['query']['bool']['must'][]['term']['ip'] = (string)$filterParams['input_form']->ip;
        }

        if ($filterParams['input_form']->phone != '') {
            $sparams['body']['query']['bool']['must'][]['term']['phone'] = $filterParams['input_form']->phone;
        }

        if ($filterParams['input_form']->email != '') {
            $sparams['body']['query']['bool']['must'][]['term']['email'] = $filterParams['input_form']->email;
        }

        if ($filterParams['input_form']->region != '') {
            $sparams['body']['query']['bool']['must'][]['term']['region'] = $filterParams['input_form']->region;
        }

        if (trim((string)$filterParams['input_form']->user_id) != '') {
            $sparams['body']['query']['bool']['must'][]['term']['user_id'] = (int)trim($filterParams['input_form']->user_id);
        }

        if (trim((string)$filterParams['input_form']->department_id) != '') {
            $sparams['body']['query']['bool']['must'][]['term']['dep_id'] = (int)trim($filterParams['input_form']->department_id);
        }

        if (trim((string)$filterParams['input_form']->invitation_id) != '') {
            $sparams['body']['query']['bool']['must'][]['term']['invitation_id'] = (int)trim($filterParams['input_form']->invitation_id);
        }

        if (trim((string)$filterParams['input_form']->cls_us) != '') {
            $sparams['body']['query']['bool']['must'][]['term']['cls_us'] = (int)trim($filterParams['input_form']->cls_us);
        }

        if (trim((string)$filterParams['input_form']->has_unread_op_messages) != '') {
            $sparams['body']['query']['bool']['must'][]['term']['has_unread_op_messages'] = (int)trim($filterParams['input_form']->has_unread_op_messages);
        }

        if ($filterParams['input_form']->dropped_chat == true) {
            $sparams['body']['query']['bool']['must'][]['term']['drpd'] = 1;
        }

        if ($filterParams['input_form']->abandoned_chat == true) {
            $sparams['body']['query']['bool']['must'][]['term']['abnd'] = 1;
        }

        if (trim((string)$filterParams['input_form']->department_group_id) != '') {
            $db = \ezcDbInstance::get();
            $stmt = $db->prepare('SELECT dep_id FROM lh_departament_group_member WHERE dep_group_id = :group_id');
            $stmt->bindValue( ':group_id', $filterParams['input']->department_group_id, \PDO::PARAM_INT);
            $stmt->execute();
            $depIds = $stmt->fetchAll(\PDO::FETCH_COLUMN);

            if (!empty($depIds)) {
                $sparams['body']['query']['bool']['must'][]['terms']['dep_id'] = $depIds;
            }
        }

        if (isset($filterParams['input']->group_id) && is_numeric($filterParams['input']->group_id) && $filterParams['input']->group_id > 0 ) {
            $db = \ezcDbInstance::get();
            $stmt = $db->prepare('SELECT user_id FROM lh_groupuser WHERE group_id = :group_id');
            $stmt->bindValue( ':group_id', $filterParams['input']->group_id, \PDO::PARAM_INT);
            $stmt->execute();
            $userIds = $stmt->fetchAll(\PDO::FETCH_COLUMN);

            if (!empty($userIds)) {
                $sparams['body']['query']['bool']['must'][]['terms']['user_id'] = $userIds;
            }
        }

        if (isset($filterParams['input']->country_ids) && is_array($filterParams['input']->country_ids) && !empty($filterParams['input']->country_ids)) {

            \erLhcoreClassChat::validateFilterInString($filterParams['input']->country_ids);

            if (!empty($filterParams['input']->country_ids)) {
                $sparams['body']['query']['bool']['must'][]['terms']['country_code'] = $filterParams['input']->country_ids;
            }
        }

        if (isset($filterParams['input']->chat_status_ids) && is_array($filterParams['input']->chat_status_ids) && !empty($filterParams['input']->chat_status_ids)) {

            \erLhcoreClassChat::validateFilterInString($filterParams['input']->chat_status_ids);

            if (!empty($filterParams['input']->chat_status_ids)) {
                $sparams['body']['query']['bool']['must'][]['terms']['status'] = $filterParams['input']->chat_status_ids;
            }
        }

        if (isset($filterParams['input']->group_ids) && is_array($filterParams['input']->group_ids) && !empty($filterParams['input']->group_ids)) {

            \erLhcoreClassChat::validateFilterIn($filterParams['input']->group_ids);

            $db = \ezcDbInstance::get();
            $stmt = $db->prepare('SELECT user_id FROM lh_groupuser WHERE group_id IN (' . implode(',',$filterParams['input']->group_ids) .')');
            $stmt->execute();
            $userIds = $stmt->fetchAll(\PDO::FETCH_COLUMN);

            if (!empty($userIds)) {
                $sparams['body']['query']['bool']['must'][]['terms']['user_id'] = $userIds;
            }
        }

        if (isset($filterParams['input']->department_group_ids) && is_array($filterParams['input']->department_group_ids) && !empty($filterParams['input']->department_group_ids)) {

            \erLhcoreClassChat::validateFilterIn($filterParams['input']->department_group_ids);

            $db = \ezcDbInstance::get();
            $stmt = $db->prepare('SELECT dep_id FROM lh_departament_group_member WHERE dep_group_id IN (' . implode(',',$filterParams['input']->department_group_ids) . ')');
            $stmt->execute();
            $depIds = $stmt->fetchAll(\PDO::FETCH_COLUMN);

            if (!empty($depIds)) {
                $sparams['body']['query']['bool']['must'][]['terms']['dep_id'] = $depIds;
            }
        }

        if (isset($filterParams['input']->department_ids) && is_array($filterParams['input']->department_ids) && !empty($filterParams['input']->department_ids)) {
            \erLhcoreClassChat::validateFilterIn($filterParams['input']->department_ids);
            $sparams['body']['query']['bool']['must'][]['terms']['dep_id'] = $filterParams['input']->department_ids;
        }

        if (isset($filterParams['input']->theme_ids) && is_array($filterParams['input']->theme_ids) && !empty($filterParams['input']->theme_ids)) {
            \erLhcoreClassChat::validateFilterIn($filterParams['input']->theme_ids);
            $sparams['body']['query']['bool']['must'][]['terms']['theme_id'] = $filterParams['input']->theme_ids;
        }

        if (isset($filterParams['input']->user_ids) && is_array($filterParams['input']->user_ids) && !empty($filterParams['input']->user_ids)) {
            \erLhcoreClassChat::validateFilterIn($filterParams['input']->user_ids);
            $sparams['body']['query']['bool']['must'][]['terms']['user_id'] = $filterParams['input']->user_ids;
        }

        if ($filterParams['input_form']->no_user == 1) {
            $sparams['body']['query']['bool']['must'][]['term']['user_id'] = 0;
        }

        if (isset($filterParams['filter']['filtergte']['time'])) {
            $sparams['body']['query']['bool']['must'][]['range']['time']['gte'] = $filterParams['filter']['filtergte']['time'] * 1000;
            $dateFilter['gte'] = $filterParams['filter']['filtergte']['time'];
        } else {
            $filterParams['filter']['filtergte']['time'] = time() - (3 * 31 * 24 * 3600);
            $sparams['body']['query']['bool']['must'][]['range']['time']['gte'] = $filterParams['filter']['filtergte']['time'] * 1000;
            $dateFilter['gte'] = $filterParams['filter']['filtergte']['time'];
        }

        if (isset($filterParams['filter']['filterlte']['time'])) {
            $sparams['body']['query']['bool']['must'][]['range']['time']['lte'] = $filterParams['filter']['filterlte']['time'] * 1000;
            $dateFilter['lte'] = $filterParams['filter']['filterlte']['time'];
        }

        if (isset($filterParams['filter']['filtergt']['chat_duration'])) {
            $sparams['body']['query']['bool']['must'][]['range']['chat_duration']['gt'] = (int)$filterParams['filter']['filtergt']['chat_duration'];
        }

        if (isset($filterParams['filter']['filterlte']['chat_duration'])) {
            $sparams['body']['query']['bool']['must'][]['range']['chat_duration']['lte'] = (int)$filterParams['filter']['filterlte']['chat_duration'];
        }

        if (isset($filterParams['filter']['filtergt']['wait_time'])) {
            $sparams['body']['query']['bool']['must'][]['range']['wait_time']['gt'] = (int)$filterParams['filter']['filtergt']['wait_time'];
        }

        if (isset($filterParams['filter']['filterlte']['wait_time'])) {
            $sparams['body']['query']['bool']['must'][]['range']['wait_time']['lte'] = (int)$filterParams['filter']['filterlte']['wait_time'];
        }

        if (isset($filterParams['filter']['filtergt']['frt'])) {
            $sparams['body']['query']['bool']['must'][]['range']['frt']['gt'] = (int)$filterParams['filter']['filtergt']['frt'];
        }

        if (isset($filterParams['filter']['filterlte']['frt'])) {
            $sparams['body']['query']['bool']['must'][]['range']['frt']['lte'] = (int)$filterParams['filter']['filterlte']['frt'];
        }

        if (isset($filterParams['filter']['filtergt']['aart'])) {
            $sparams['body']['query']['bool']['must'][]['range']['aart']['gt'] = (int)$filterParams['filter']['filtergt']['aart'];
        }

        if (isset($filterParams['filter']['filterlte']['aart'])) {
            $sparams['body']['query']['bool']['must'][]['range']['aart']['lte'] = (int)$filterParams['filter']['filterlte']['aart'];
        }

        if (isset($filterParams['filter']['filtergt']['mart'])) {
            $sparams['body']['query']['bool']['must'][]['range']['mart']['gt'] = (int)$filterParams['filter']['filtergt']['mart'];
        }

        if (isset($filterParams['filter']['filterlte']['mart'])) {
            $sparams['body']['query']['bool']['must'][]['range']['mart']['lte'] = (int)$filterParams['filter']['filterlte']['mart'];
        }

        if ($filterParams['input_form']->has_operator == 1) {
            $sparams['body']['query']['bool']['must'][]['range']['user_id']['gt'] = (int)0;
        }

        if ($filterParams['input_form']->with_bot == 1) {
            $sparams['body']['query']['bool']['must'][]['range']['gbot_id']['gt'] = (int)0;
        }

        if ($filterParams['input_form']->transfer_happened == 1) {
            $sparams['body']['query']['bool']['must'][]['range']['transfer_uid']['gt'] = (int)0;
            $sparams['body']['query']['bool']['must'][]['range']['user_id']['gt'] = (int)0;
            $sparams['body']['query']['bool']['filter']['script']['script'] = "doc['user_id'].value != doc['transfer_uid'].value";
        }

        if ($filterParams['input_form']->without_bot == 1) {
            $sparams['body']['query']['bool']['must'][]['term']['gbot_id'] = 0;
        }

        if (isset(\erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionElasticsearch')->settings['columns'])) {
            foreach (\erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionElasticsearch')->settings['columns'] as $columnField => $columnData) {
                if ($columnData['filter_type'] == 'filterstring') {
                    if (isset($filterParams['input_form']->{$columnField}) && trim($filterParams['input_form']->{$columnField}) != '') {
                        $sparams['body']['query']['bool']['must'][]['term'][$columnData['field_search']] = (string)$filterParams['input_form']->{$columnField};
                    }
                } elseif ($columnData['filter_type'] == 'filterrangefloatgt') {
                    if (isset($filterParams['input_form']->{$columnField}) && trim($filterParams['input_form']->{$columnField}) != '') {
                        $sparams['body']['query']['bool']['must'][]['range'][$columnData['field_search']]['gt'] = (float)$filterParams['input_form']->{$columnField};
                    }
                } elseif ($columnData['filter_type'] == 'filterrangefloatlt') {
                    if (isset($filterParams['input_form']->{$columnField}) && trim($filterParams['input_form']->{$columnField}) != '') {
                        $sparams['body']['query']['bool']['must'][]['range'][$columnData['field_search']]['lt'] = (float)$filterParams['input_form']->{$columnField};
                    }
                }
            }
        }

        if (isset($filterParams['input']->bot_ids) && is_array($filterParams['input']->bot_ids) && !empty($filterParams['input']->bot_ids)) {

            \erLhcoreClassChat::validateFilterInString($filterParams['input']->bot_ids);

            if (!empty($filterParams['input']->bot_ids)) {
                $sparams['body']['query']['bool']['must'][]['terms']['gbot_id'] = $filterParams['input']->bot_ids;
            }
        }

        if (isset($filterParams['input']->iwh_ids) && is_array($filterParams['input']->iwh_ids) && !empty($filterParams['input']->iwh_ids)) {

            \erLhcoreClassChat::validateFilterInString($filterParams['input']->iwh_ids);

            if (!empty($filterParams['input']->iwh_ids)) {
                $sparams['body']['query']['bool']['must'][]['terms']['iwh_id'] = $filterParams['input']->iwh_ids;
            }
        }

        if (isset($filterParams['input']->subject_id) && is_array($filterParams['input']->subject_id) && !empty($filterParams['input']->subject_id)) {

            \erLhcoreClassChat::validateFilterInString($filterParams['input']->subject_id);

            if (!empty($filterParams['input']->subject_id)) {
                $sparams['body']['query']['bool']['must'][]['terms']['subject_id'] = $filterParams['input']->subject_id;
            }
        }

        if (trim((string)$filterParams['input_form']->uagent) != '') {
            $sparams['body']['query']['bool']['must'][]['match']['uagent'] = $filterParams['input_form']->uagent;
        }

        $filesFilter = array();
        if ($filterParams['input_form']->hvf == 1) {
            $filesFilter[]['term']['hvf'] = 1;
        }

        if ($filterParams['input_form']->hof == 1) {
            $filesFilter[]['term']['hof'] = 1;
        }

        if (!empty($filesFilter)) {
            $sparams['body']['query']['bool']['must'][]['bool']['should'] = $filesFilter;
        }

        // From what page customer start a chat
        if (trim((string)$filterParams['input_form']->referrer) != '') {
            $sparams['body']['query']['bool']['should'][]['match']['referrer'] = $filterParams['input_form']->referrer;
            $sparams['body']['query']['bool']['minimum_should_match'] = 1; // Minimum one condition should be matched
        }

        // From what page customer come to our website
        if (trim((string)$filterParams['input_form']->session_referrer) != '') {
            $sparams['body']['query']['bool']['should'][]['match']['session_referrer'] = $filterParams['input_form']->session_referrer;
            $sparams['body']['query']['bool']['minimum_should_match'] = 1; // Minimum one condition should be matched
        }

        if (trim((string)$filterParams['input_form']->keyword) != '') {

            $exactMatch = $filterParams['input_form']->exact_match == 1 ? 'match_phrase' : 'match';

            $paramQuery = [
                'query' => $filterParams['input_form']->keyword
            ];

            if ($filterParams['input_form']->fuzzy == 1 && $filterParams['input_form']->exact_match != 1 && $filterParams['input_form']->expression != 1) {
                $paramQuery['fuzziness'] = 'AUTO';
                $paramQuery['prefix_length'] = max((mb_strlen($filterParams['input_form']->keyword) - (is_numeric($filterParams['input_form']->fuzzy_prefix) ? $filterParams['input_form']->fuzzy_prefix : 1)),0);
            }

            if (empty($filterParams['input_form']->search_in) || in_array(1,$filterParams['input_form']->search_in)) {

                if ($filterParams['input_form']->expression == 1) {
                    $queryStringParam = $paramQuery;
                    // Did not worked on my ES version. Perhaps to old.
                    //$queryStringParam["default_field"] = ["msg_visitor","msg_operator","msg_system"];
                    $sparams['body']['query']['bool']['must'][]["query_string"] = $queryStringParam;
                } else {
                    $sparams['body']['query']['bool']['should'][][$exactMatch]['msg_visitor'] = $paramQuery;
                    $sparams['body']['query']['bool']['should'][][$exactMatch]['msg_operator'] = $paramQuery;
                    $sparams['body']['query']['bool']['should'][][$exactMatch]['msg_system'] = $paramQuery;
                }

            } else {
                if (in_array(2,$filterParams['input_form']->search_in)) {
                    $sparams['body']['query']['bool']['should'][][$exactMatch]['msg_visitor'] = $paramQuery;
                }

                if (in_array(3,$filterParams['input_form']->search_in)) {
                    $sparams['body']['query']['bool']['should'][][$exactMatch]['msg_operator'] = $paramQuery;
                }

                if (in_array(4,$filterParams['input_form']->search_in)) {
                    $sparams['body']['query']['bool']['should'][][$exactMatch]['msg_system'] = $paramQuery;
                }
            }

            if ($filterParams['input_form']->expression != 1) {
                $sparams['body']['query']['bool']['minimum_should_match'] = 1; // Minimum one condition should be matched
            }

            $sparams['body']['highlight']['order'] = 'score';
            $sparams['body']['highlight']['fragment_size'] = 40;
            $sparams['body']['highlight']['number_of_fragments'] = 5;
            $sparams['body']['highlight']['fields']['msg_operator'] = new \stdClass();
            $sparams['body']['highlight']['fields']['msg_visitor'] = new \stdClass();
            $sparams['body']['highlight']['fields']['msg_system'] = new \stdClass();
        }

        if (is_numeric($filterParams['input_form']->id_gt)) {
            $sparams['body']['query']['bool']['must'][]['range']['chat_id']['gt'] = $filterParams['input_form']->id_gt;
        }

        \erLhcoreClassChatEventDispatcher::getInstance()->dispatch('elasticsearch.chatsearchexecute',array('sparams' => & $sparams, 'filter' => $filterParams));

        if ($filterParams['input_form']->sort_chat == 'asc') {
            $sort = array('time' => array('order' => 'asc'));
        } elseif ($filterParams['input_form']->sort_chat == 'relevance') {
            $sort = array('_score' => array('order' => 'desc'));
        } else {
            $sort = array('time' => array('order' => 'desc'));
        }

        if (trim((string)$filterParams['input_form']->chat_id) != '') {
            foreach ($chat_ids as $index_chat_id => $chat_id) {
                $chatDirect = \erLhcoreClassModelChat::fetch((int)trim($chat_id));

                if (!($chatDirect instanceof \erLhcoreClassModelChat)) {
                    $chatArchive = \erLhcoreClassChatArcive::fetchChatById((int)trim($chat_id));
                    if (is_array($chatArchive)) {
                        $chatDirect = $chatArchive['chat'];
                    }
                }

                if (is_object($chatDirect)) {
                    if ($index_chat_id == 0) {
                        $sparams = array(
                            'body' => array()
                        );
                        $sparams['body']['query']['bool']['must'][]['terms']['chat_id'] = $chat_ids;
                    }

                    if (isset($dateFilter['gte'])) {
                        $dateFilter['gte'] = min($chatDirect->time + 10, $dateFilter['gte']);
                    } else {
                        $dateFilter['gte'] = $chatDirect->time + 10;
                    }

                    if (isset($dateFilter['lte'])) {
                        $dateFilter['lte'] = max($chatDirect->time - 10,$dateFilter['lte']);
                    } else {
                        $dateFilter['lte'] = $chatDirect->time - 10;
                    }
                }
            }
        }

        $append = \erLhcoreClassSearchHandler::getURLAppendFromInput($filterParams['input_form'],false,[],['keyword']);

        $chats = \erLhcoreClassModelESChat::getList(array(
            'offset' => (isset($_GET['offset']) ? (int)$_GET['offset'] : 0),
            'limit' => (isset($_GET['limit']) ? (int)$_GET['limit'] : 20),
            'body' => array_merge(array(
                'sort' => $sort
            ), $sparams['body'])
        ),
            array('date_index' => $dateFilter));

        $chatIds = array();
        foreach ($chats as $prevChat) {
            $chatIds[$prevChat->chat_id] = array();
        }

        \erLhcoreClassChatArcive::setArchiveAttribute($chatIds);

        $prefillFields = [];
        if (isset($_GET['prefill_fields'])){
            $prefillFields = explode(',',str_replace(' ','',$_GET['prefill_fields']));
        }

        $ignoreFields = array();
        if (isset($_GET['ignore_fields'])){
            $ignoreFields = explode(',',str_replace(' ','',$_GET['ignore_fields']));
        }

        if (!empty($prefillFields) || !empty($ignoreFields)) {
            \erLhcoreClassChat::prefillGetAttributes($chats, $prefillFields, $ignoreFields, array('clean_ignore' => true, 'do_not_clean' => true));
        }

        if (isset($_GET['include_messages']) && $_GET['include_messages'] == 'true' && !empty($chats)) {
            $messages = \erLhcoreClassModelmsg::getList(array('limit' => 100000,'sort' => 'id ASC','filterin' => array('chat_id' => array_keys($chats))));
            foreach ($messages as $message) {
                if (!is_array($chats[$message->chat_id]->messages)) {
                    $chats[$message->chat_id]->messages = array();
                }
                if (isset($_GET['meta_parse']) && ($_GET['meta_parse'] == 'true' && $_GET['meta_parse'] == '1') && $message->msg == '') {
                    $metaMessage = $message->meta_msg_array;
                    if (is_array($metaMessage) && isset($metaMessage['content']) && is_array($metaMessage['content'])) {
                        if (isset($metaMessage['content']['text_conditional']['full_op'])) {
                            $message->msg = trim(preg_replace('/\[button_action=not_insult\](.*)\[\/button_action\]/is','',$metaMessage['content']['text_conditional']['full_op']));
                            unset($message->meta_msg_array);
                        }
                    }
                }
                $chats[$message->chat_id]->messages[] = $message;
            }
        }

        if (isset($_GET['include_survey']) && $_GET['include_survey'] == 'true' && !empty($chats)) {
            $filledSurveys = \erLhAbstractModelSurveyItem::getList(array('limit' => 100000,'sort' => 'id ASC','filterin' => array('chat_id' => array_keys($chats))));
            foreach ($filledSurveys as $filledSurvey) {
                $chats[$filledSurvey->chat_id]->survey = $filledSurvey;
            }
        }

        $chatsCount = 0;
        if (isset($_GET['count_records']) && $_GET['count_records'] == 'true') {
            $chatsCount = \erLhcoreClassModelESChat::getCount($sparams, array('date_index' => $dateFilter));
        }

        return array(
            'date_index' => $dateFilter,
            'url' => $append,
            'filter' => $sparams,
            'list_count' => $chatsCount,
            'error' => false,
            'list' => array_values($chats),
        );
    }
    
    public static function validateConversationList()
    {
        // Conversations filter
        $filterParams = \erLhcoreClassSearchHandler::getParams(array(
            'customfilterfile' => 'extension/elasticsearch/classes/filter/mail_list.php',
            'format_filter' => true,
            'use_override' => true,
            'uparams' => []
        ));

        $sparams = array(
            'body' => array()
        );

        $dateFilter = array();

        if (trim((string)$filterParams['input_form']->conversation_id) != '') {
            $chat_ids = explode(',',trim($filterParams['input_form']->conversation_id));
            \erLhcoreClassChat::validateFilterIn($chat_ids);

            // Merged id's support
            // In the future once we have archiving this part has to support archives
            $chat_ids = array_filter($chat_ids);

            if (!empty($chat_ids)) {
                $idsRelated = array_unique(\erLhcoreClassModelMailconvMessage::getCount(['filter' => ['conversation_id_old' => $chat_ids]], '', false, 'conversation_id', false, true, true));
                if (!empty($idsRelated)) {
                    $chat_ids = array_merge($chat_ids,$idsRelated);
                }
            }

            if (!empty($chat_ids)) {
                $sparams['body']['query']['bool']['must'][]['terms']['conversation_id'] = $chat_ids;
            }
        }

        if (is_numeric($filterParams['input_form']->has_attachment)) {
            if ($filterParams['input_form']->has_attachment == \erLhcoreClassModelMailconvConversation::ATTACHMENT_MIX) {
                $sparams['body']['query']['bool']['must'][]['terms']['has_attachment_conv'] = [
                    \erLhcoreClassModelMailconvConversation::ATTACHMENT_INLINE,
                    \erLhcoreClassModelMailconvConversation::ATTACHMENT_FILE,
                    \erLhcoreClassModelMailconvConversation::ATTACHMENT_MIX
                ];
            } else if ($filterParams['input_form']->has_attachment == \erLhcoreClassModelMailconvConversation::ATTACHMENT_INLINE) {
                $sparams['body']['query']['bool']['must'][]['terms']['has_attachment_conv'] = [
                    \erLhcoreClassModelMailconvConversation::ATTACHMENT_INLINE,
                    \erLhcoreClassModelMailconvConversation::ATTACHMENT_MIX
                ];
            } else if ($filterParams['input_form']->has_attachment == \erLhcoreClassModelMailconvConversation::ATTACHMENT_FILE) {
                $sparams['body']['query']['bool']['must'][]['terms']['has_attachment_conv'] = [
                    \erLhcoreClassModelMailconvConversation::ATTACHMENT_FILE,
                    \erLhcoreClassModelMailconvConversation::ATTACHMENT_MIX
                ];
            } else if ($filterParams['input_form']->has_attachment == \erLhcoreClassModelMailconvConversation::ATTACHMENT_EMPTY) {
                $sparams['body']['query']['bool']['must'][]['term']['has_attachment_conv'] = \erLhcoreClassModelMailconvConversation::ATTACHMENT_EMPTY;
            } else if ($filterParams['input_form']->has_attachment == 5) { // No attachment (inline)
                $sparams['body']['query']['bool']['must_not'][]['terms']['has_attachment_conv'] = [
                    \erLhcoreClassModelMailconvConversation::ATTACHMENT_INLINE,
                    \erLhcoreClassModelMailconvConversation::ATTACHMENT_MIX
                ];
            } else if ($filterParams['input_form']->has_attachment == 4) { // No attachment (as file)
                $sparams['body']['query']['bool']['must_not'][]['terms']['has_attachment_conv'] = [
                    \erLhcoreClassModelMailconvConversation::ATTACHMENT_FILE,
                    \erLhcoreClassModelMailconvConversation::ATTACHMENT_MIX
                ];
            }
        }

        if (trim((string)$filterParams['input_form']->message_id) != '') {
            $sparams['body']['query']['bool']['must'][]['term']['id'] = (int)trim($filterParams['input_form']->message_id);
        }

        if ($filterParams['input_form']->from_name != '') {
            $sparams['body']['query']['bool']['must'][]['match']['from_name'] = $filterParams['input_form']->from_name;
        }

        if ($filterParams['input_form']->phone != '') {
            $sparams['body']['query']['bool']['must'][]['term']['phone'] = $filterParams['input_form']->phone;
        }

        if ($filterParams['input_form']->email != '') {
            if (empty($filterParams['input_form']->search_email_in) || $filterParams['input_form']->search_email_in == 1) {
                $sparams['body']['query']['bool']['must'][]['term']['customer_address_clean'] = \erLhcoreClassElasticSearchIndex::cleanEmail(trim($filterParams['input_form']->email));
            } elseif ($filterParams['input_form']->search_email_in == 2) {
                $sparams['body']['query']['bool']['must'][]['term']['from_address_clean'] = \erLhcoreClassElasticSearchIndex::cleanEmail(trim($filterParams['input_form']->email));
            } elseif ($filterParams['input_form']->search_email_in == 3) {
                $sparams['body']['query']['bool']['must'][]['term']['from_address'] = trim($filterParams['input_form']->email);
            } elseif ($filterParams['input_form']->search_email_in == 4) {
                $sparams['body']['query']['bool']['must'][]['term']['customer_address'] = trim($filterParams['input_form']->email);
            }
        }

        if ($filterParams['input_form']->sender_host != '') {
            $sparams['body']['query']['bool']['must'][]['term']['sender_host'] = trim($filterParams['input_form']->sender_host);
        }

        if ($filterParams['input_form']->sender_address != '') {
            $sparams['body']['query']['bool']['must'][]['term']['sender_address'] = trim($filterParams['input_form']->sender_address);
        }

        if ($filterParams['input_form']->from_host != '') {
            $sparams['body']['query']['bool']['must'][]['term']['from_host'] = trim($filterParams['input_form']->from_host);
        }

        if (trim((string)$filterParams['input_form']->user_id) != '') {
            $sparams['body']['query']['bool']['must'][]['term']['conv_user_id'] = (int)trim($filterParams['input_form']->user_id);
        }

        if (trim((string)$filterParams['input_form']->response_type) != '') {
            $sparams['body']['query']['bool']['must'][]['term']['response_type'] = (int)trim($filterParams['input_form']->response_type);
        }

        if (trim((string)$filterParams['input_form']->status) != '') {
            $sparams['body']['query']['bool']['must'][]['term']['status'] = (int)trim($filterParams['input_form']->status);
        }

        if (trim((string)$filterParams['input_form']->opened) != '') {
            if ($filterParams['input_form']->opened === 0) {
                $sparams['body']['query']['bool']['must_not'][]['range']['opened_at']['gte'] = 1;
            } else {
                $sparams['body']['query']['bool']['must'][]['range']['opened_at']['gte'] = 1;
            }
        }

        if (trim((string)$filterParams['input_form']->status_conv) != '') {
            $sparams['body']['query']['bool']['must'][]['term']['status_conv'] = (int)trim($filterParams['input_form']->status_conv);
        }

        if (trim((string)$filterParams['input_form']->department_id) != '') {
            $sparams['body']['query']['bool']['must'][]['term']['dep_id'] = (int)trim($filterParams['input_form']->department_id);
        }

        if (is_numeric($filterParams['input_form']->is_external)) {
            $sparams['body']['query']['bool']['must'][]['term']['is_external'] = $filterParams['input_form']->is_external;
        }

        if (is_numeric($filterParams['input_form']->id_gt)) {
            $sparams['body']['query']['bool']['must'][]['range']['conversation_id']['gt'] = $filterParams['input_form']->id_gt;
        }

        if (trim((string)$filterParams['input_form']->department_group_id) != '') {
            $db = \ezcDbInstance::get();
            $stmt = $db->prepare('SELECT dep_id FROM lh_departament_group_member WHERE dep_group_id = :group_id');
            $stmt->bindValue( ':group_id', $filterParams['input']->department_group_id, \PDO::PARAM_INT);
            $stmt->execute();
            $depIds = $stmt->fetchAll(\PDO::FETCH_COLUMN);

            if (!empty($depIds)) {
                $sparams['body']['query']['bool']['must'][]['terms']['dep_id'] = $depIds;
            }
        }

        if (isset($filterParams['input']->group_id) && is_numeric($filterParams['input']->group_id) && $filterParams['input']->group_id > 0 ) {
            $db = \ezcDbInstance::get();
            $stmt = $db->prepare('SELECT user_id FROM lh_groupuser WHERE group_id = :group_id');
            $stmt->bindValue( ':group_id', $filterParams['input']->group_id, \PDO::PARAM_INT);
            $stmt->execute();
            $userIds = $stmt->fetchAll(\PDO::FETCH_COLUMN);

            if (!empty($userIds)) {
                $sparams['body']['query']['bool']['must'][]['terms']['conv_user_id'] = $userIds;
            }
        }

        if (isset($filterParams['input']->group_ids) && is_array($filterParams['input']->group_ids) && !empty($filterParams['input']->group_ids)) {

            \erLhcoreClassChat::validateFilterIn($filterParams['input']->group_ids);

            $db = \ezcDbInstance::get();
            $stmt = $db->prepare('SELECT user_id FROM lh_groupuser WHERE group_id IN (' . implode(',',$filterParams['input']->group_ids) .')');
            $stmt->execute();
            $userIds = $stmt->fetchAll(\PDO::FETCH_COLUMN);

            if (!empty($userIds)) {
                $sparams['body']['query']['bool']['must'][]['terms']['conv_user_id'] = $userIds;
            }
        }

        if (isset($filterParams['input']->department_group_ids) && is_array($filterParams['input']->department_group_ids) && !empty($filterParams['input']->department_group_ids)) {

            \erLhcoreClassChat::validateFilterIn($filterParams['input']->department_group_ids);

            $db = \ezcDbInstance::get();
            $stmt = $db->prepare('SELECT dep_id FROM lh_departament_group_member WHERE dep_group_id IN (' . implode(',',$filterParams['input']->department_group_ids) . ')');
            $stmt->execute();
            $depIds = $stmt->fetchAll(\PDO::FETCH_COLUMN);

            if (!empty($depIds)) {
                $sparams['body']['query']['bool']['must'][]['terms']['dep_id'] = $depIds;
            }
        }

        if (isset($filterParams['input']->department_ids) && is_array($filterParams['input']->department_ids) && !empty($filterParams['input']->department_ids)) {
            \erLhcoreClassChat::validateFilterIn($filterParams['input']->department_ids);
            $sparams['body']['query']['bool']['must'][]['terms']['dep_id'] = $filterParams['input']->department_ids;
        }

        if (isset($filterParams['input']->lang_ids) && is_array($filterParams['input']->lang_ids) && !empty($filterParams['input']->lang_ids)) {
            $sparams['body']['query']['bool']['must'][]['terms']['lang'] = $filterParams['input']->lang_ids;
        }

        if (isset($filterParams['input']->user_ids) && is_array($filterParams['input']->user_ids) && !empty($filterParams['input']->user_ids)) {
            \erLhcoreClassChat::validateFilterIn($filterParams['input']->user_ids);
            $sparams['body']['query']['bool']['must'][]['terms']['conv_user_id'] = $filterParams['input']->user_ids;
        }

        if ($filterParams['input_form']->no_user == 1) {
            $sparams['body']['query']['bool']['must'][]['term']['conv_user_id'] = 0;
        }

        if ($filterParams['input_form']->undelivered == 1) {
            $sparams['body']['query']['bool']['must'][]['term']['undelivered'] = 1;
        }

        if (isset($filterParams['filter']['filtergte']['time'])) {
            $sparams['body']['query']['bool']['must'][]['range']['time']['gte'] = $filterParams['filter']['filtergte']['time'] * 1000;
            $dateFilter['gte'] = $filterParams['filter']['filtergte']['time'];
        } else {
            $filterParams['filter']['filtergte']['time'] = time() - (3 * 31 * 24 * 3600);
            $sparams['body']['query']['bool']['must'][]['range']['time']['gte'] = $filterParams['filter']['filtergte']['time'] * 1000;
            $dateFilter['gte'] = $filterParams['filter']['filtergte']['time'];
        }

        if (isset($filterParams['filter']['filterlte']['time'])) {
            $sparams['body']['query']['bool']['must'][]['range']['time']['lte'] = $filterParams['filter']['filterlte']['time'] * 1000;
            $dateFilter['lte'] = $filterParams['filter']['filterlte']['time'];
        }

        if ($filterParams['input_form']->has_operator == 1) {
            $sparams['body']['query']['bool']['must'][]['range']['conv_user_id']['gt'] = 0;
        }

        if ($filterParams['input_form']->is_followup == 1) {
            $sparams['body']['query']['bool']['must'][]['range']['follow_up_id']['gt'] = 0;
        }

        if (isset($filterParams['input']->subject_id) && is_array($filterParams['input']->subject_id) && !empty($filterParams['input']->subject_id)) {
            \erLhcoreClassChat::validateFilterInString($filterParams['input']->subject_id);
            if (!empty($filterParams['input']->subject_id)) {
                $sparams['body']['query']['bool']['must'][]['terms']['subject_id'] = $filterParams['input']->subject_id;
            }
        }

        if (isset($filterParams['input']->ids) && is_array($filterParams['input']->ids) && !empty($filterParams['input']->ids)) {
            \erLhcoreClassChat::validateFilterInString($filterParams['input']->ids);
            if (!empty($filterParams['input']->ids)) {
                $sparams['body']['query']['bool']['must'][]['terms']['conversation_id'] = $filterParams['input']->ids;
            }
        }

        if (isset($filterParams['input']->status_conv_id) && is_array($filterParams['input']->status_conv_id) && !empty($filterParams['input']->status_conv_id)) {
            \erLhcoreClassChat::validateFilterInString($filterParams['input']->status_conv_id);
            if (!empty($filterParams['input']->status_conv_id)) {
                $sparams['body']['query']['bool']['must'][]['terms']['status_conv'] = $filterParams['input']->status_conv_id;
            }
        }

        if (isset($filterParams['input']->status_msg_id) && is_array($filterParams['input']->status_msg_id) && !empty($filterParams['input']->status_msg_id)) {
            \erLhcoreClassChat::validateFilterInString($filterParams['input']->status_msg_id);
            if (!empty($filterParams['input']->status_msg_id)) {
                $sparams['body']['query']['bool']['must'][]['terms']['status'] = $filterParams['input']->status_msg_id;
            }
        }

        if (isset($filterParams['input']->mailbox_ids) && is_array($filterParams['input']->mailbox_ids) && !empty($filterParams['input']->mailbox_ids)) {
            \erLhcoreClassChat::validateFilterInString($filterParams['input']->mailbox_ids);
            if (!empty($filterParams['input']->mailbox_ids)) {
                $sparams['body']['query']['bool']['must'][]['terms']['mailbox_id'] = $filterParams['input']->mailbox_ids;
            }
        }

        if ($filterParams['input_form']->hvf == 1) {
            $sparams['body']['query']['bool']['must'][]['terms']['has_attachment'] = [1,2,3];
        }

        if (trim((string)$filterParams['input_form']->keyword) != '') {

            $exactMatch = $filterParams['input_form']->exact_match == 1 ? 'match_phrase' : 'match';

            $paramQuery = [
                'query' => $filterParams['input_form']->keyword
            ];

            if ($filterParams['input_form']->fuzzy == 1 && $filterParams['input_form']->exact_match != 1 && $filterParams['input_form']->expression != 1) {
                $paramQuery['fuzziness'] = 'AUTO';
                $paramQuery['prefix_length'] = max((mb_strlen($filterParams['input_form']->keyword) - (is_numeric($filterParams['input_form']->fuzzy_prefix) ? $filterParams['input_form']->fuzzy_prefix : 1)),0);
            }

            if (empty($filterParams['input_form']->search_in)) {
                if ($filterParams['input_form']->expression == 1) {
                    $queryStringParam = $paramQuery;
                    $sparams['body']['query']['bool']['must'][]["query_string"] = $queryStringParam;
                } else {
                    $sparams['body']['query']['bool']['should'][][$exactMatch]['subject'] = $paramQuery;
                    $sparams['body']['query']['bool']['should'][][$exactMatch]['alt_body'] = $paramQuery;
                }
            } else {
                if (in_array(1,$filterParams['input_form']->search_in)) {
                    $sparams['body']['query']['bool']['should'][][$exactMatch]['subject'] = $paramQuery;
                }

                if (in_array(2,$filterParams['input_form']->search_in)) {
                    $sparams['body']['query']['bool']['should'][][$exactMatch]['alt_body'] = $paramQuery;
                }

                if (in_array(3,$filterParams['input_form']->search_in)) {
                    $sparams['body']['query']['bool']['should'][][$exactMatch]['from_name'] = $paramQuery;
                }

                if (in_array(4,$filterParams['input_form']->search_in)) {
                    $sparams['body']['query']['bool']['should'][][$exactMatch]['sender_name'] = $paramQuery;
                }

                if (in_array(5,$filterParams['input_form']->search_in)) {
                    $sparams['body']['query']['bool']['should'][][$exactMatch]['delivery_status'] = $paramQuery;
                }

                if (in_array(6,$filterParams['input_form']->search_in)) {
                    $sparams['body']['query']['bool']['should'][][$exactMatch]['rfc822_body'] = $paramQuery;
                }

                if (in_array(7,$filterParams['input_form']->search_in)) {
                    $sparams['body']['query']['bool']['should'][][$exactMatch]['reply_to_data'] = $paramQuery;
                }

                if (in_array(8,$filterParams['input_form']->search_in)) {
                    $sparams['body']['query']['bool']['should'][][$exactMatch]['to_data'] = $paramQuery;
                }

                if (in_array(9,$filterParams['input_form']->search_in)) {
                    $sparams['body']['query']['bool']['should'][][$exactMatch]['cc_data'] = $paramQuery;
                }

                if (in_array(10,$filterParams['input_form']->search_in)) {
                    $sparams['body']['query']['bool']['should'][][$exactMatch]['bcc_data'] = $paramQuery;
                }

                if (in_array(11,$filterParams['input_form']->search_in)) {
                    $sparams['body']['query']['bool']['should'][][$exactMatch]['mb_folder'] = $paramQuery;
                }

                if (in_array(12,$filterParams['input_form']->search_in)) {
                    $sparams['body']['query']['bool']['should'][][$exactMatch]['customer_name'] = $paramQuery;
                }
            }

            if ($filterParams['input_form']->expression != 1) {
                $sparams['body']['query']['bool']['minimum_should_match'] = 1; // Minimum one condition should be matched
            }


            $sparams['body']['highlight']['order'] = 'score';
            $sparams['body']['highlight']['fragment_size'] = 40;
            $sparams['body']['highlight']['number_of_fragments'] = 5;

            $sparams['body']['highlight']['fields']['subject'] = new \stdClass();
            $sparams['body']['highlight']['fields']['alt_body'] = new \stdClass();
        }

        \erLhcoreClassChatEventDispatcher::getInstance()->dispatch('elasticsearch.mailsearchexecute',array('sparams' => & $sparams, 'filter' => $filterParams));

        if ($filterParams['input_form']->sort_chat == 'asc') {
            $sort = array('conversation_id' => array('order' => 'asc'));
        } elseif ($filterParams['input_form']->sort_chat == 'lastupdateasc') {
            $sort = array('time' => array('order' => 'asc'));
        } elseif ($filterParams['input_form']->sort_chat == 'lastupdatedesc') {
            $sort = array('time' => array('order' => 'desc'));
        } elseif ($filterParams['input_form']->sort_chat == 'relevance') {
            $sort = array('_score' => array('order' => 'desc'));
        } else {
            $sort = array('conversation_id' => array('order' => 'desc'));
        }

        $append = \erLhcoreClassSearchHandler::getURLAppendFromInput($filterParams['input_form'],false,[],['keyword']);

        $chats = \erLhcoreClassModelESMail::getList(array(
            'offset' => (isset($_GET['offset']) ? (int)$_GET['offset'] : 0),
            'limit' => (isset($_GET['limit']) ? (int)$_GET['limit'] : 20),
            'body' => array_merge(array(
                'sort' => $sort
            ), $sparams['body'])
        ),
        array('date_index' => $dateFilter));

        $previousConversation = null;
        foreach ($chats as $prevChat) {
            if (is_object($previousConversation) && $previousConversation->conversation_id == $prevChat->conversation_id) {
                $previousConversation->has_many_messages = true;
            }
            $previousConversation = $prevChat;
        }

        $prefillFields = [];
        if (isset($_GET['prefill_fields'])){
            $prefillFields = explode(',',str_replace(' ','',$_GET['prefill_fields']));
        }

        if (in_array('mailbox',$prefillFields) && !\erLhcoreClassRestAPIHandler::hasAccessTo('lhmailconv','mailbox_manage')) {
            throw new \Exception('You do not have access to manage mailbox! \'lhmailconv\',\'mailbox_manage\'');
        }

        $ignoreFields = array();
        if (isset($_GET['ignore_fields'])) {
            $ignoreFields = explode(',',str_replace(' ','',$_GET['ignore_fields']));
        }

        if (!empty($prefillFields) || !empty($ignoreFields)) {
            \erLhcoreClassChat::prefillGetAttributes($chats, $prefillFields, $ignoreFields, array('clean_ignore' => true, 'do_not_clean' => true));
        }

        $mailsCount = 0;

        if (isset($_GET['count_records']) && $_GET['count_records'] == 'true') {
            $mailsCount = \erLhcoreClassModelESMail::getCount($sparams, array('date_index' => $dateFilter));
        }

        return array(
            'date_index' => $dateFilter,
            'url' => $append,
            'filter' => $sparams,
            'list_count' => $mailsCount,
            'error' => false,
            'list' => array_values($chats),
        );

    }
}