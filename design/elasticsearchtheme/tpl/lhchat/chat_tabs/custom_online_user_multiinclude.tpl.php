<?php
    $isEnabledElasticUsername = (int)erLhcoreClassModelChatConfig::fetch('elasticsearch_options')->data['use_es_prev_chats'];
    $isElasticDisabled = (int)erLhcoreClassModelChatConfig::fetch('elasticsearch_options')->data['disable_es'];
?>
<?php if ($isEnabledElasticUsername == true && $isElasticDisabled == false) : ?>
<li role="presentation" class="nav-item"><a class="nav-link" href="#online-user-info-eschats-tab-<?php echo $chat->id?>" aria-controls="online-user-info-eschats-tab-<?php echo $chat->id?>" role="tab" data-bs-toggle="tab" title="Chats" aria-expanded="true"><i class="material-icons me-0">chat</i></a></li>
<?php endif; ?>