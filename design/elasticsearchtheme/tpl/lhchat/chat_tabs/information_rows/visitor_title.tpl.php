<h6 class="fw-bold">
    <?php if ($chat->email != '') : ?>
        <a target="_blank" href="<?php echo erLhcoreClassDesign::baseurl('elasticsearch/interactions')?>/(attr)/email/(val)/<?php echo rawurlencode($chat->email) ?>" class="text-muted">
        <i class="material-icons">open_in_new</i>
    <?php else : ?>
        <i class="material-icons">face</i>
    <?php endif; ?>
    <?php if (isset($chat->chat_variables_array['nick_secure']) && $chat->chat_variables_array['nick_secure'] == true) : ?>
        <i class="material-icons" title="<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/adminchat','Passed as encrypted variable')?>">enhanced_encryption</i>
    <?php endif; ?>
    <?php if ($chat->nick != 'Visitor') : ?><?php echo htmlspecialchars($chat->nick)?><?php else : ?><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/adminchat','Visitor')?><?php endif; ?>
    <?php if ($chat->email != '') : ?></a><?php endif; ?>
</h6>