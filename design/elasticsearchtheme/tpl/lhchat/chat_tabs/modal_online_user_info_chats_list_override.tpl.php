<?php
$isEnabledElasticChatId = (int)erLhcoreClassModelChatConfig::fetch('elasticsearch_options')->data['use_es_prev_chats_id'];
$isElasticDisabled = (int)erLhcoreClassModelChatConfig::fetch('elasticsearch_options')->data['disable_es'];
?>
<?php if ($isEnabledElasticChatId == false && $isElasticDisabled == false) : ?>
    <?php include(erLhcoreClassDesign::designtpl('lhchat/chat_tabs/online_user_info_chats_list_list.tpl.php'));?>
<?php else : ?>

    <?php $idHistory = isset($chat_id_present) && is_numeric($chat_id_present) ? $chat_id_present : ($online_user->chat_id > 0 ? $online_user->chat_id : $online_user->id); ?>

    <input type="hidden" id="use-elastic-prev-chatid-<?php echo $idHistory?>" value="0">
    <div id="use-elastic-prev-chatid-content-<?php echo $idHistory?>"></div>

    <script>
    $.getJSON(lhinst.wwwDir + "elasticsearch/getpreviouschatsbyid/<?php echo $idHistory,(((isset($chat_id_present) && is_numeric($chat_id_present)) || $online_user->chat_id > 0) ? '' : '/(type)/ou')?>", function (data) {
        $('#use-elastic-prev-chatid-content-<?php echo $idHistory?>').html(data.result);
    });
    </script>

<?php endif; ?>