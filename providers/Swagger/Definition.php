<?php

namespace LiveHelperChatExtension\elasticsearch\providers\Swagger;

class Definition
{
    public static function setDefinition($params)
    {
        $params['append_paths'] .= ',"/restapi/elasticconversations": {
      "get": {
        "tags": [
          "mail"
        ],
        "summary": "Fetch mail messages list. It searches for mail messages.",
        "description": "Required permissions - `lhmailconv`,`use_admin`. List content depends on assigned API user departments.",
        "produces": [
          "application/json"
        ],
        "parameters": [
          {
            "name": "department_ids[]",
            "in": "query",
            "description": "Department ID\'s",
            "required": false,
            "type": "array",
            "items":{
              "type":"integer"
            },
            "collectionFormat": "multi"
          },
          {
            "name": "department_group_ids[]",
            "in": "query",
            "description": "Department group ID\'s",
            "required": false,
            "type": "array",
            "items":{
              "type":"integer"
            },
            "collectionFormat": "multi"
          },
          {
            "name": "user_ids[]",
            "in": "query",
            "description": "User IDs",
            "required": false,
            "type": "array",
            "items":{
              "type":"integer"
            },
            "collectionFormat": "multi"
          },
          {
            "name": "ids[]",
            "in": "query",
            "description": "Conversations IDs",
            "required": false,
            "type": "array",
            "items":{
              "type":"integer"
            },
            "collectionFormat": "multi"
          },
          {
            "name": "mailbox_ids[]",
            "in": "query",
            "description": "Mailbox IDs",
            "required": false,
            "type": "array",
            "items": {
              "type": "integer"
            },
            "collectionFormat": "multi"
          },
          {
            "name": "status_conv_id[]",
            "in": "query",
            "description": "Conversation status. const STATUS_PENDING = 0; const STATUS_ACTIVE = 1; const STATUS_CLOSED = 2;",
            "required": false,
            "type": "array",
            "items": {
              "type": "integer"
            },
            "collectionFormat": "multi"
          },
          {
            "name": "status_msg_id[]",
            "in": "query",
            "description": "Message status. const STATUS_PENDING = 0; const STATUS_ACTIVE = 1; const STATUS_RESPONDED = 2;",
            "required": false,
            "type": "array",
            "items": {
              "type": "integer"
            },
            "collectionFormat": "multi"
          },
          {
            "name": "subject_id[]",
            "in": "query",
            "description": "Subject ID",
            "required": false,
            "type": "array",
            "items": {
              "type": "integer"
            },
            "collectionFormat": "multi"
          },
          {
            "name": "limit",
            "in": "query",
            "description": "Limit",
            "required": false,
            "type": "string",
            "format": "int32"
          },
          {
            "name": "offset",
            "in": "query",
            "description": "Offset",
            "required": false,
            "type": "string",
            "format": "int32"
          },
          {
            "name": "id_gt",
            "in": "query",
            "description": "ID greater than. It is a conversation id. Not a mail message ID.",
            "required": false,
            "type": "string",
            "format": "int32"
          },
          {
            "name": "is_external",
            "in": "query",
            "description": "Sender. 0 - We, 1 - Visitor",
            "required": false,
            "type": "string",
            "format": "int32"
          },
          {
            "name": "timefromts",
            "in": "query",
            "description": "Time greater than. Unix timestamp. udate >=",
            "required": false,
            "type": "string",
            "format": "int32"
          },
          {
            "name": "timetots",
            "in": "query",
            "description": "Time less than. Unix timestamp. udate <=",
            "required": false,
            "type": "string",
            "format": "int32"
          },
          {
            "name": "count_records",
            "description": "Count total records by filter",
            "required": false,
            "type": "boolean",
            "default": false,
            "in": "query"
          },
          {
            "name": "exact_match",
            "description": "Exact match phrase",
            "required": false,
            "type": "boolean",
            "default": false,
            "in": "query"
          },
          {
            "name": "expression",
            "description": "Expression search",
            "required": false,
            "type": "boolean",
            "default": false,
            "in": "query"
          },
          {
            "name": "fuzzy",
            "description": "Fuzzy search",
            "required": false,
            "type": "boolean",
            "default": false,
            "in": "query"
          },
          {
            "name": "fuzzy_prefix",
            "in": "query",
            "description": "Length of keyword minus n character",
            "required": false,
            "type": "string",
            "format": "int32"
          },
          {
            "name": "phone",
            "description": "Phone",
            "required": false,
            "type": "string",
            "in": "query"
          },
          {
            "name": "email",
            "description": "E-mail",
            "required": false,
            "type": "string",
            "in": "query"
          },
          {
            "name": "from_name",
            "description": "From name",
            "required": false,
            "type": "string",
            "in": "query"
          },
          {
            "name": "keyword",
            "description": "Keyword",
            "required": false,
            "type": "string",
            "in": "query"
          },
          {
            "name": "search_in[]",
            "in": "query",
            "description": "Search in. Search in. 1 - Subject, 2 - Body, 3 - From Name, 4 - Sender Name, 5 - Delivery Status, 6 - RFC822 Body (Undelivered mail body), 7 - Reply To Data, 8 - To Data, 9 - CC Data, 10 - BCC Data, 11 - Mailbox Folder, 12 - Customer Name",
            "required": false,
            "type": "array",
            "items": {
              "type": "integer"
            },
            "collectionFormat": "multi"
          },
          {
            "name": "prefill_fields",
            "description": "What fields to prefill E.g mail_variables_array, customer_email, interaction_time_duration, plain_user_name, user, last_mail_front, conv_duration_front, wait_time_response,wait_time_pending, department_name, subject_front, mailbox, department, pnd_time_front, ctime_front, udate_front, accept_time_front, cls_time_front, lr_time_front, pnd_time_front_ago, ctime_front_ago, udate_front_ago, accept_time_front_ago, cls_time_front_ago, lr_time_front_ago",
            "required": false,
            "type": "string",
            "in": "query"
          },
          {
            "name": "ignore_fields",
            "description": "What fields to skip E.g mail_variables_array, customer_email, interaction_time_duration, plain_user_name, user, last_mail_front, conv_duration_front, wait_time_response,wait_time_pending, department_name, subject_front, mailbox, department, pnd_time_front, ctime_front, udate_front, accept_time_front, cls_time_front, lr_time_front, pnd_time_front_ago, ctime_front_ago, udate_front_ago, accept_time_front_ago, cls_time_front_ago, lr_time_front_ago",
            "required": false,
            "type": "string",
            "in": "query"
          }
          {{elastic_mail_search_parameters}}
        ],
        "responses": {
          "200": {
            "description": "Fetch mail conversations list",
            "schema": {
            }
          },
          "400": {
            "description": "Error",
            "schema": {
            }
          }
        },
        "security": [
          {
            "login": []
          }
        ]
      }
    },"/restapi/elasticchats": {
      "get": {
        "tags": [
          "chat"
        ],
        "summary": "Fetch chats. It searches for chats in ElasticSearch",
        "description": "Required permissions - `lhmailconv`,`use_admin`. List content depends on assigned API user departments.",
        "produces": [
          "application/json"
        ],
        "parameters": [
          {{chats_parameters}}{
            "name": "department_id",
            "in": "query",
            "description": "Department ID",
            "required": false,
            "type": "string",
            "format": "int32"
          },
          {
            "name": "department_ids[]",
            "in": "query",
            "description": "Department ID\'s",
            "required": false,
            "type": "array",
            "items":{
              "type":"integer"
            },
            "collectionFormat": "multi"
          },
          {
            "name": "department_group_ids[]",
            "in": "query",
            "description": "Department group ID\'s",
            "required": false,
            "type": "array",
            "items":{
              "type":"integer"
            },
            "collectionFormat": "multi"
          },
          {
            "name": "user_id",
            "in": "query",
            "description": "User ID",
            "required": false,
            "type": "string",
            "format": "int32"
          },
          {
            "name": "user_ids[]",
            "in": "query",
            "description": "User IDs",
            "required": false,
            "type": "array",
            "items":{
              "type":"integer"
            },
            "collectionFormat": "multi"
          },
          {
            "name": "phone",
            "in": "query",
            "description": "Phone",
            "required": false,
            "type": "string",
            "format": "int32"
          },
          {
            "name": "email",
            "in": "query",
            "description": "Email",
            "required": false,
            "type": "string",
            "format": "int32"
          },
          {
            "name": "nick",
            "in": "query",
            "description": "Nick",
            "required": false,
            "type": "string",
            "format": "int32"
          },
          {
            "name": "chat_status_ids[]",
            "in": "query",
            "description": "chat_status_ids. \nconst STATUS_PENDING_CHAT = 0;\n   const STATUS_ACTIVE_CHAT = 1;\n   const STATUS_CLOSED_CHAT = 2;\n   const STATUS_CHATBOX_CHAT = 3;\n   const STATUS_OPERATORS_CHAT = 4;\n   const STATUS_BOT_CHAT = 5;",
            "required": false,
            "type": "array",
            "items":{
              "type":"integer"
            },
             "collectionFormat": "multi"
          },
          {
            "name": "has_operator",
            "description": "Has operator",
            "required": false,
            "type": "boolean",
            "default": "",
            "in": "query"
          },
          {
            "name": "with_bot",
            "description": "Chats which had a bot",
            "required": false,
            "type": "boolean",
            "default": "",
            "in": "query"
          },
          {
            "name": "without_bot",
            "description": "Chats which did not had a bot",
            "required": false,
            "type": "boolean",
            "default": "",
            "in": "query"
          },
          {
            "name": "bot_ids[]",
            "description": "Bot ID\'s",
            "required": false,
            "default": "",
            "in": "query",
            "type": "array",
            "items":{
              "type":"integer"
            },
             "collectionFormat": "multi"
          },
          {
            "name": "limit",
            "in": "query",
            "description": "Limit",
            "required": false,
            "type": "string",
            "format": "int32"
          },
          {
            "name": "offset",
            "in": "query",
            "description": "Offset",
            "required": false,
            "type": "string",
            "format": "int32"
          },
          {
            "name": "count_records",
            "description": "Count total records by filter",
            "required": false,
            "type": "boolean",
            "default": false,
            "in": "query"
          },
          {
            "name": "id_gt",
            "in": "query",
            "description": "ID greater than",
            "required": false,
            "type": "string",
            "format": "int32"
          },
          {
            "name": "include_messages",
            "description": "Include messages within chat",
            "required": false,
            "type": "boolean",
            "default": false,
            "in": "query"
          },
          {
            "name": "include_survey",
            "description": "Include survey",
            "required": false,
            "type": "boolean",
            "default": false,
            "in": "query"
          },
          {
            "name": "timefromts",
            "in": "query",
            "description": "Time greater than. Unix timestamp. time >=",
            "required": false,
            "type": "string",
            "format": "int32"
          },
          {
            "name": "timetots",
            "in": "query",
            "description": "Time less than. Unix timestamp. time <=",
            "required": false,
            "type": "string",
            "format": "int32"
          },
          {
            "name": "prefill_fields",
            "description": "What fields to prefill E.g messages_statistic, subject, link, time_created_front, department_name, plain_user_name, product_name, n_official, n_off_full, wait_time_pending, wait_time_seconds, cls_time_front, status_sub_sub, can_edit_chat, unread_time, chat_actions",
            "required": false,
            "type": "string",
            "in": "query"
          },
          {
            "name": "ignore_fields",
            "description": "What fields to skip E.g product_id, product, department, time, status, user, additional_data, additional_data_array, chat_variables, chat_variables_array",
            "required": false,
            "type": "string",
            "in": "query"
          },
          {
            "name": "keyword",
            "description": "Keyword",
            "required": false,
            "type": "string",
            "in": "query"
          },
          {
            "name": "search_in[]",
            "in": "query",
            "description": "Search in. Search in. 2 - Visitor messages, 3 - Operator messages, 4 - System messages",
            "required": false,
            "type": "array",
            "items": {
              "type": "integer"
            },
            "collectionFormat": "multi"
          },
          {
            "name": "exact_match",
            "description": "Exact match phrase",
            "required": false,
            "type": "boolean",
            "default": false,
            "in": "query"
          },
          {
            "name": "expression",
            "description": "Expression search",
            "required": false,
            "type": "boolean",
            "default": false,
            "in": "query"
          },
          {
            "name": "fuzzy",
            "description": "Fuzzy search",
            "required": false,
            "type": "boolean",
            "default": false,
            "in": "query"
          },
          {
            "name": "fuzzy_prefix",
            "in": "query",
            "description": "Length of keyword minus n character",
            "required": false,
            "type": "string",
            "format": "int32"
          }
          {{elastic_chat_search_parameters}}
        ],
        "responses": {
          "200": {
            "description": "Fetch chat list",
            "schema": {
            }
          },
          "400": {
            "description": "Error",
            "schema": {
            }
          }
        },
        "security": [
          {
            "login": []
          }
        ]
      }
    }';

    }
}