<div id="tabs" role="tabpanel">
<?php if (isset($Result['path'])) :
    $pathElementCount = count($Result['path'])-1;
    if ($pathElementCount >= 0): ?>
        <div id="path-container" style="margin-left: -8px;margin-right: -7px" ng-non-bindable>
            <ul class="breadcrumb rounded-0 border-bottom p-2 mb-0" itemscope itemtype="http://data-vocabulary.org/Breadcrumb">
                <li class="breadcrumb-item"><a rel="home" itemprop="url" href="<?php echo erLhcoreClassDesign::baseurl()?>"><span itemprop="title"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('pagelayout/pagelayout','Home')?></span></a></li>
                <?php foreach ($Result['path'] as $key => $pathItem) : if (isset($pathItem['url']) && $pathElementCount != $key) { ?><li class="breadcrumb-item" itemscope itemtype="http://data-vocabulary.org/Breadcrumb"><a href="<?php echo $pathItem['url']?>" itemprop="url"><span itemprop="title"><?php echo htmlspecialchars(htmlspecialchars_decode($pathItem['title'],ENT_QUOTES))?></span></a></li><?php } else { ?><li class="breadcrumb-item" itemscope itemtype="http://data-vocabulary.org/Breadcrumb"><span itemprop="title"><?php echo htmlspecialchars(htmlspecialchars_decode($pathItem['title'], ENT_QUOTES))?></span></li><?php }; ?><?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
<?php endif;?>

    <ul class="nav nav-pills" role="tablist">
        <li role="presentation" class="nav-item"><a class="nav-link active" href="#chats" aria-controls="chats" role="tab" data-bs-toggle="tab"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('user/account','Interactions');?></a></li>
    </ul>

    <div class="tab-content ps-2">
        <div role="tabpanel" class="tab-pane active" id="chats">
            <?php include(erLhcoreClassDesign::designtpl('elasticsearch/parts/filter_interactions.tpl.php')); ?>

            <?php if (isset($pages) && $pages->items_total > 0): ?>
                <table class="table table-sm mt-1 list-links">
                    <thead>
                    <tr>
                        <th width="8%"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','ID')?></th>
                        <th width="65%"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Highlight')?></th>
                        <th width="21%"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Department')?></th>
                        <th width="1%"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Time')?></th>
                    </tr>
                    </thead>
                    <?php foreach ($items as $item) : ?>
                        <tr>
                            <td nowrap="nowrap">
                                <?php if ($item instanceof erLhcoreClassModelESChat) : ?>
                                    <a onclick="lhc.previewChat(<?php echo $item->chat_id?>)"><i class="material-icons">info_outline</i></a>
                                    <a class="action-image material-icons" data-title="<?php echo htmlspecialchars($item->nick,ENT_QUOTES);?>" onclick="<?php if (isset($itemsArchive[$item->chat_id]) && $itemsArchive[$item->chat_id]['archive'] == true) : ?>lhinst.startChatNewWindowArchive('<?php echo $itemsArchive[$item->chat_id]['archive_id']?>','<?php echo $item->chat_id;?>',$(this).attr('data-title'))<?php else : ?>lhinst.startChatNewWindow('<?php echo $item->chat_id;?>',$(this).attr('data-title'))<?php endif;?>" title="<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/pendingchats','Open in a new window');?>">open_in_new</a>
                                    <a href="#!#Fchat-id-<?php echo $item->chat_id?>" ng-click="lhc.startChatByID(<?php echo $item->chat_id?>)"><span class="material-icons">chat</span><?php echo $item->chat_id?></a>
                                <?php elseif ($item instanceof erLhcoreClassModelESMail) : ?>
                                    <a href="#!#Fchat-id-<?php echo $item->conversation_id?>" onclick="lhc.previewMail(<?php echo $item->conversation_id?>);" class="material-icons">info_outline</a>
                                    <a class="action-image material-icons" data-title="<?php echo htmlspecialchars($item->subject)?>" onclick="lhinst.startMailNewWindow(<?php echo $item->conversation_id?>,$(this).attr('data-title'))" >open_in_new</a>
                                    <a class="user-select-none" ng-click='lhc.startMailChat(<?php echo $item->conversation_id?>,<?php echo json_encode($item->subject_front,JSON_HEX_APOS)?>)' href="#!#chat-id-mc<?php echo $item->conversation_id?>"><span class="material-icons">email</span><?php echo $item->conversation_id?> <?php echo htmlspecialchars(erLhcoreClassDesign::shrt($item->subject,50))?></a>
                                <?php else : ?>
                                    <?php include(erLhcoreClassDesign::designtpl('elasticsearch/parts/interactions_type_multiinclude.tpl.php')); ?>
                                <?php endif ?>

                                <?php if (erLhcoreClassUser::instance()->hasAccessTo('lhelasticsearch','configure')) : ?>
                                    <a title="Raw information" href="<?php echo erLhcoreClassDesign::baseurl('elasticsearch/raw')?>/<?php echo $item->meta_data['index']?>/<?php echo $item->id?>"><i class="material-icons">&#xE86F;</i></a>
                                <?php endif; ?>
                            </td>
                            <td ng-non-bindable>
                                <?php if (isset($item->meta_data['highlight'])) : ?>
                                    <div class="abbr-list-general action-image">
                                        <?php foreach ($item->meta_data['highlight'] as $field => $fields) :
                                            $highlightText = erLhcoreClassBBCode::make_clickable(htmlspecialchars(str_replace(array('<em>','</em>'),array('[mark]','[/mark]'),implode("\n",$fields)))); ?>
                                            <div>
                                                <?php if ($field == 'msg_system') : ?>
                                                    <i>System:</i>
                                                <?php elseif ($field == 'subject') : ?>
                                                    <i>Subject:</i>
                                                <?php elseif ($field == 'alt_body') : ?>
                                                    <i>Body:</i>
                                                <?php elseif ($field == 'msg_operator') : ?>
                                                    <i>Operator:</i>
                                                <?php else : ?>
                                                    <i>Visitor:</i>
                                                <?php endif; ?>
                                                <?php echo $highlightText;?></div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                                <?php if (is_array($item->subjects) && !empty($item->subjects)) : ?>
                                    <div>
                                        <?php foreach ($item->subjects as $subject) : ?>
                                            <span class="badge bg-info mx-1" ng-non-bindable><?php echo htmlspecialchars($subject)?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($item->department)?></td>
                            <td nowrap="nowrap"><?php echo date(erLhcoreClassModule::$dateDateHourFormat, $item->time/1000)?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>

                <?php include(erLhcoreClassDesign::designtpl('lhkernel/secure_links.tpl.php')); ?>

                <?php include(erLhcoreClassDesign::designtpl('lhkernel/paginator.tpl.php')); ?>

            <?php else: ?>

                <br>
                <div class="alert alert-info"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','No records were found or search was not executed yet!')?></div>

            <?php endif; ?>
        </div>
    </div>


</div>