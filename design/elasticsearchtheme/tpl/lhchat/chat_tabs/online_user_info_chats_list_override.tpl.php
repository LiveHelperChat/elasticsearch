<?php
    $isEnabledElasticChatId = (int)erLhcoreClassModelChatConfig::fetch('elasticsearch_options')->data['use_es_prev_chats_id'];
    $isElasticDisabled = (int)erLhcoreClassModelChatConfig::fetch('elasticsearch_options')->data['disable_es'];
?>
<?php if ($isEnabledElasticChatId == false && $isElasticDisabled == false) : ?>
    <?php include(erLhcoreClassDesign::designtpl('lhchat/chat_tabs/online_user_info_chats_list_list.tpl.php'));?>
<?php else : ?>
    <input type="hidden" id="use-elastic-prev-chatid-<?php echo $chat->id?>" value="0">
    <div id="use-elastic-prev-chatid-content-<?php echo $chat->id?>"></div>
<?php endif; ?>
