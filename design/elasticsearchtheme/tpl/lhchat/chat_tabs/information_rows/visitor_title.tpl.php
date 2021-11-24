<h6 class="font-weight-bold">
    <?php if ($chat->email != '') : ?>
        <a target="_blank" href="<?php echo erLhcoreClassDesign::baseurl('elasticsearch/interactions')?>/(attr)/email/(val)/<?php echo rawurlencode($chat->email) ?>" class="text-dark">
        <i class="material-icons">open_in_new</i>
    <?php else : ?>
        <i class="material-icons">face</i>
    <?php endif; ?>
    <?php if ($chat->nick != 'Visitor') : ?><?php echo htmlspecialchars($chat->nick)?><?php else : ?><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/adminchat','Visitor')?><?php endif; ?>
    <?php if ($chat->email != '') : ?></a><?php endif; ?>
</h6>