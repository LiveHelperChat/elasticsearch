<?php if ($search->scope == 'eschat') : ?>
    <?php if (!$list_mode) : ?>
        <div role="tabpanel" id="tabs" ng-cloak>
        <ul class="nav nav-pills" role="tablist">
            <li role="presentation" class="nav-item"><a class="nav-link active" href="#chatlist" aria-controls="chatlist" role="tab" data-bs-toggle="tab" title="<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/onlineusers','Chat list');?>">
                    <?php echo htmlspecialchars($search->name)?> </a>
            </li>
        </ul>
        <div class="tab-content" ng-cloak>
        <div role="tabpanel" class="tab-pane form-group active" id="chatlist">
        <div id="view-content-list">
    <?php endif; ?>

    <table class="table table-sm">
        <thead>
        <tr>
            <th width="10%"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/pendingchats','Information');?></th>
            <th width="45%" nowrap>
                <?php include(erLhcoreClassDesign::designtpl('elasticsearch/list/nick_title.tpl.php')); ?>
            </th>
            <th width="15%"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/pendingchats','Operator');?></th>
            <th width="10%"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Department')?></th>
            <th width="10%"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/pendingchats','Status');?></th>
            <th width="5%"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Date')?></th>
        </tr>
        </thead>
        <?php foreach ($items as $item) : ?>
            <tr>
                <td nowrap="nowrap">

                    <?php $chat = $item;?>
                    <?php include(erLhcoreClassDesign::designtpl('lhchat/lists/icons_additional.tpl.php')); ?>

                    <?php foreach ($chat->aicons as $aicon) : ?>
                        <i class="material-icons" style="color: <?php isset($aicon['c']) ? print htmlspecialchars($aicon['c']) : print '#6c757d'?>" title="<?php isset($aicon['t']) ? print htmlspecialchars($aicon['t']) : htmlspecialchars($aicon['i'])?> {{icon.t ? icon.t : icon.i}}"><?php isset($aicon['i']) ? print htmlspecialchars($aicon['i']) : htmlspecialchars($aicon)?></i>
                    <?php endforeach; ?>

                    <?php $chatArchivePreview = false; if (isset($itemsArchive[$item->chat_id]) && $itemsArchive[$item->chat_id]['archive'] == true) : $chatArchivePreview = true;?>
                        <a onclick="lhc.previewChatArchive(<?php echo $itemsArchive[$item->chat_id]['archive_id']?>,<?php echo $item->chat_id?>)"><i class="material-icons">info_outline</i></a>
                        <a class="action-image material-icons" data-title="<?php echo htmlspecialchars($item->nick,ENT_QUOTES);?>" onclick="<?php if (isset($itemsArchive[$item->chat_id]) && $itemsArchive[$item->chat_id]['archive'] == true) : ?>lhinst.startChatNewWindowArchive('<?php echo $itemsArchive[$item->chat_id]['archive_id']?>','<?php echo $item->chat_id;?>',$(this).attr('data-title'))<?php else : ?>lhinst.startChatNewWindow('<?php echo $item->chat_id;?>',$(this).attr('data-title'))<?php endif;?>" title="<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/pendingchats','Open in a new window');?>">open_in_new</a>
                        <a href="<?php echo erLhcoreClassDesign::baseurl('chatarchive/viewarchivedchat')?>/<?php echo $itemsArchive[$item->chat_id]['archive_id']?>/<?php echo $item->chat_id;?>"><?php echo $item->chat_id?></a>
                    <?php else : ?>
                        <a onclick="lhc.previewChat(<?php echo $item->chat_id?>)"><i class="material-icons">info_outline</i></a>
                        <a class="action-image material-icons" data-title="<?php echo htmlspecialchars($item->nick,ENT_QUOTES);?>" onclick="<?php if (isset($itemsArchive[$item->chat_id]) && $itemsArchive[$item->chat_id]['archive'] == true) : ?>lhinst.startChatNewWindowArchive('<?php echo $itemsArchive[$item->chat_id]['archive_id']?>','<?php echo $item->chat_id;?>',$(this).attr('data-title'))<?php else : ?>lhinst.startChatNewWindow('<?php echo $item->chat_id;?>',$(this).attr('data-title'))<?php endif;?>" title="<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/pendingchats','Open in a new window');?>">open_in_new</a>
                        <a href="#" onclick="ee.emitEvent('angularStartChatbyId',[<?php echo $item->chat_id?>])" ><?php echo $item->chat_id?></a>
                    <?php endif; ?>

                    <?php if (erLhcoreClassUser::instance()->hasAccessTo('lhelasticsearch','configure')) : ?>
                        <a title="Raw information" href="<?php echo erLhcoreClassDesign::baseurl('elasticsearch/raw')?>/<?php echo $item->meta_data['index']?>/<?php echo $item->id?>"><i class="material-icons">&#xE86F;</i></a>
                    <?php endif; ?>

                </td>
                <td nowrap="nowrap" ng-non-bindable>
                    <?php include(erLhcoreClassDesign::designtpl('elasticsearch/list/nick.tpl.php')); ?>
                </td>
                <td><?php echo htmlspecialchars($item->user);?></td>
                <td nowrap="nowrap"><?php echo htmlspecialchars($item->department)?></td>
                <td nowrap="nowrap">
                    <?php if ($item->fbst == 1) : ?><i class="material-icons up-voted">thumb_up</i><?php elseif ($item->fbst == 2) : ?><i class="material-icons down-voted">thumb_down<i><?php endif;?>
                            <?php if ($item->status == erLhcoreClassModelChat::STATUS_PENDING_CHAT) : ?>
                                <i class="material-icons chat-pending">chat</i><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/pendingchats','Pending chat');?>
                            <?php elseif ($item->status == erLhcoreClassModelChat::STATUS_ACTIVE_CHAT) : ?>
                                <i class="material-icons chat-active">chat</i><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/pendingchats','Active chat');?>
                            <?php elseif ($item->status == erLhcoreClassModelChat::STATUS_CLOSED_CHAT) : ?>
                                <i class="material-icons chat-closed">chat</i><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/pendingchats','Closed chat');?>
                            <?php elseif ($item->status == erLhcoreClassModelChat::STATUS_CHATBOX_CHAT) : ?>
                                <i class="material-icons chat-active">chat</i><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/pendingchats','Chatbox chat');?>
                            <?php elseif ($item->status == erLhcoreClassModelChat::STATUS_OPERATORS_CHAT) : ?>
                                <i class="material-icons chat-active">face</i><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/pendingchats','Operators chat');?>
                            <?php elseif ($item->status == erLhcoreClassModelChat::STATUS_BOT_CHAT) : ?>
                                <i class="material-icons chat-active">android</i><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/pendingchats','Bot chat');?>
                            <?php endif;?>
                            <?php include(erLhcoreClassDesign::designtpl('lhchat/lists_chats_parts/status_multiinclude.tpl.php'));?>
                </td>
                <td nowrap="nowrap">
                    <?php echo erLhcoreClassChat::formatSeconds(time() - ($item->time/1000))?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

    <?php if (isset($pages)) : ?>
        <?php include(erLhcoreClassDesign::designtpl('lhkernel/paginator.tpl.php')); ?>
    <?php endif;?>

    <?php include(erLhcoreClassDesign::designtpl('lhkernel/secure_links.tpl.php')); ?>

    <?php if (!$list_mode) : ?>
        </div>
        </div>
        </div>
        </div>
    <?php endif; ?>

<?php else : ?>

<?php endif; ?>
