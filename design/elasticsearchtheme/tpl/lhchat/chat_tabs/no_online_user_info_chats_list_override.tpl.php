<?php
$isEnabledElasticUsername = (int)erLhcoreClassModelChatConfig::fetch('elasticsearch_options')->data['use_es_prev_chats'];
$isElasticDisabled = (int)erLhcoreClassModelChatConfig::fetch('elasticsearch_options')->data['disable_es'];
?>
<?php if ($isEnabledElasticUsername == true && $isElasticDisabled == false && isset($chat_id_present) && is_numeric($chat_id_present)) : ?>

<div id="online-user-info-eschats-tab-<?php echo $chat_id_present?>"></div>

<script>
    $.getJSON(lhinst.wwwDir + "elasticsearch/getpreviouschats/<?php echo $chat_id_present?>", function (data) {
        $('#online-user-info-eschats-tab-<?php echo $chat_id_present?>').append(data.result);
    });
</script>

<?php endif; ?>