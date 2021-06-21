<ul class="foot-print-content list-unstyled" style="max-height: 170px;" ng-non-bindable>
    <?php $hasPrevChat = false; foreach ($chatsPrev as $chatPrev) : ?>
        <?php if (!isset($chat) || $chat->id != $chatPrev->chat_id) : $hasPrevChat = true;?>
            <li>
                <?php if ( !empty($chatPrev->country_code) ) : ?><img src="<?php echo erLhcoreClassDesign::design('images/flags');?>/<?php echo $chatPrev->country_code?>.png" alt="<?php echo htmlspecialchars($chatPrev->country_name)?>" title="<?php echo htmlspecialchars($chatPrev->country_name)?>" />&nbsp;<?php endif; ?>
                <a title="<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/syncadmininterface','Open in a new window');?>" class="material-icons" onclick="<?php if (isset($chatsPrevArchives[$chatPrev->chat_id]) && $chatsPrevArchives[$chatPrev->chat_id]['archive'] == true) : ?>lhinst.startChatNewWindowArchive('<?php echo $chatsPrevArchives[$chatPrev->chat_id]['archive_id']?>','<?php echo $chatPrev->chat_id;?>',$(this).attr('data-title'))<?php else : ?>lhinst.startChatNewWindow('<?php echo $chatPrev->chat_id;?>',$(this).attr('data-title'))<?php endif;?>" data-title="<?php echo htmlspecialchars($chatPrev->nick,ENT_QUOTES);?>">open_in_new</a><?php echo $chatPrev->chat_id;?>. <?php echo htmlspecialchars($chatPrev->nick);?> (<?php echo date(erLhcoreClassModule::$dateDateHourFormat,$chatPrev->time/1000);?>) (<?php echo htmlspecialchars($chatPrev->department);?>)
            </li>
        <?php endif; ?>
    <?php endforeach;?>
</ul>

<?php if ($hasPrevChat == false) : ?>
    <?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/syncadmininterface','No previous chats');?>
<?php endif;?>

