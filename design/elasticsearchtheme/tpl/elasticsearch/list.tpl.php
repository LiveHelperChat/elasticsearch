<h1><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Chat list')?></h1>

<ul class="nav nav-pills" role="tablist">
	<li role="presentation" class="nav-item"><a class="nav-link<?php if ($tab == '' || $tab == 'chats') : ?> active<?php endif;?>" href="#chats" aria-controls="chats" role="tab" data-toggle="tab"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('user/account','Chats');?></a></li>
	<li role="presentation" class="nav-item"><a class="nav-link<?php if ($tab == 'messages') : ?> active<?php endif;?>" href="#messages" aria-controls="messages" role="tab" data-toggle="tab" ><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('user/account','Messages');?></a></li>
</ul>

<div class="tab-content" ng-non-bindable>
	<div role="tabpanel" class="tab-pane <?php if ($tab == '' || $tab == 'chats') : ?>active<?php endif;?>" id="chats">
		<?php include(erLhcoreClassDesign::designtpl('elasticsearch/parts/filter.tpl.php')); ?>
	   	<?php if ($tab == 'chats') : ?>
            <?php if (isset($pages) && $pages->items_total > 0): ?>
            	<table class="table table-sm mt-1">
            		<thead>
            			<tr>
            			    <th width="1%"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Chat ID')?></th>
            			    <th width="1%"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Nick')?></th>
            			    <th width="73%"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Highlight')?></th>
            			    <th width="21%"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Department')?></th>
            			    <th width="1%"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Time')?></th>
            			    <th width="1%"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','IP')?></th>
            			    <th width="1%"></th>
            			</tr>  
            		</thead>
            		<?php foreach ($items as $item) : ?>
            		    <tr>
            		        <td nowrap="nowrap">
            		        <a class="action-image material-icons" data-title="<?php echo htmlspecialchars($item->nick,ENT_QUOTES);?>" onclick="<?php if (isset($itemsArchive[$item->chat_id]) && $itemsArchive[$item->chat_id]['archive'] == true) : ?>lhinst.startChatNewWindowArchive('<?php echo $itemsArchive[$item->chat_id]['archive_id']?>','<?php echo $item->chat_id;?>',$(this).attr('data-title'))<?php else : ?>lhinst.startChatNewWindow('<?php echo $item->chat_id;?>',$(this).attr('data-title'))<?php endif;?>" title="<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/pendingchats','Open in a new window');?>">open_in_new</a>

                            <?php $chatArchivePreview = false; if (isset($itemsArchive[$item->chat_id]) && $itemsArchive[$item->chat_id]['archive'] == true) : $chatArchivePreview = true;?>
                                <a href="#" onclick="lhc.previewChatArchive(<?php echo $itemsArchive[$item->chat_id]['archive_id']?>,<?php echo $item->chat_id?>)"><i class="material-icons">info_outline</i></a>
                            <?php else : ?>
                                <a href="#" onclick="lhc.previewChat(<?php echo $item->chat_id?>)"><i class="material-icons">info_outline</i></a>
                            <?php endif; ?>

            		        <a href="<?php echo erLhcoreClassDesign::baseurl('elasticsearch/listmsg')?>/<?php echo $item->chat_id?>"><?php echo $item->chat_id?></a>
            		        
            		        <a title="Raw information" href="<?php echo erLhcoreClassDesign::baseurl('elasticsearch/raw')?>/<?php echo $item->meta_data['index']?>/<?php echo $item->id?>"><i class="material-icons">&#xE86F;</i></a>
            		        
            		        </td>
            		        <td nowrap="nowrap">
                                <?php include(erLhcoreClassDesign::designtpl('elasticsearch/list/nick.tpl.php')); ?>
                            </td>
                            <td>
                                <?php if (isset($item->meta_data['highlight'])) : ?>
                                <div class="abbr-list-general action-image" onclick="<?php if ($chatArchivePreview == true) : ?>lhc.previewChatArchive(<?php echo $itemsArchive[$item->chat_id]['archive_id']?>,<?php echo $item->chat_id?>)<?php else : ?>lhc.previewChat(<?php echo $item->chat_id?>)<?php endif;?>">
                                    <?php foreach ($item->meta_data['highlight'] as $field => $fields) : $highlightText = erLhcoreClassBBCode::make_clickable(htmlspecialchars(str_replace(array('<em>','</em>'),array('[mark]','[/mark]'),implode($fields,"\n")))); ?>
                                        <div>
                                            <?php if ($field == 'msg_system') : ?>
                                                <i>System:</i>
                                            <?php elseif ($field == 'msg_operator') : ?>
                                                <i>Operator:</i>
                                            <?php else : ?>
                                                <i>Visitor:</i>
                                            <?php endif; ?>
                                            <?php echo $highlightText;?></div>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($item->department)?></td>
            		        <td nowrap="nowrap"><?php echo date(erLhcoreClassModule::$dateDateHourFormat, $item->time/1000)?></td>
            		        <td><?php echo htmlspecialchars($item->ip)?></td>
                            <?php if (erLhcoreClassUser::instance()->hasAccessTo('lhelasticsearch','configure')) : ?>
            		        <td title="<?php echo htmlspecialchars($item->meta_data['index'])?>">
            		            <a class="btn btn-danger btn-xs csfr-required" onclick="return confirm('<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('kernel/messages','Are you sure?');?>')" href="<?php echo erLhcoreClassDesign::baseurl('elasticsearch/delete')?>/<?php echo $item->meta_data['index']?>/<?php echo $item->id?>"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('user/userlist','Delete');?></a>
            		        </td>
                            <?php endif; ?>
            		    </tr>
            		<?php endforeach; ?>
            	</table>
            
            	<?php include(erLhcoreClassDesign::designtpl('lhkernel/secure_links.tpl.php')); ?>
            
            	<?php include(erLhcoreClassDesign::designtpl('lhkernel/paginator.tpl.php')); ?>
            
            <?php else: ?>

            <br>
            <div class="alert alert-info"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','No records were found or search was not executed yet!')?></div>

            <?php endif; ?>
        <?php endif; ?>
    </div>
    <div role="tabpanel" class="tab-pane <?php if ($tab == 'messages') : ?>active<?php endif;?>" id="messages">
        <?php include(erLhcoreClassDesign::designtpl('elasticsearch/parts/filter_msg.tpl.php')); ?>
        
        <?php if ($tab == 'messages') : ?>
        
            <?php if ($pages->items_total > 0): ?>            
            <table class="table">
        		<thead>
        			<tr>
        			    <th width="30%"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Chat ID')?></th>
        			    <th width="30%"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Time')?></th>
        			    <th width="30%"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Message')?></th>
        			    <th width="30%"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','User ID')?></th>
        			    <th width="30%"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Indice')?></th>
        			</tr>
        		</thead>
        		<?php foreach ($items as $item) : ?>
        		    <tr>
        		        <td>
        		        <a class="action-image material-icons" data-title="<?php echo htmlspecialchars('Visitor',ENT_QUOTES);?>" onclick="lhinst.startChatNewWindow('<?php echo $item->chat_id;?>',$(this).attr('data-title'))" title="<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/pendingchats','Open in a new window');?>">open_in_new</a>
                    	      
        		        <a href="#" onclick="lhc.previewChat(<?php echo $item->chat_id?>)"><i class="material-icons">info_outline</i><?php echo $item->chat_id?>
        		        </td>
        		        <td><?php echo date(erLhcoreClassModule::$dateDateHourFormat, $item->time/1000)?></td>
        		        <td><?php echo htmlspecialchars($item->msg)?></td>
        		        <td><?php echo htmlspecialchars($item->user_id)?></td>        		       
        		        <td><?php echo htmlspecialchars($item->meta_data['index'])?></td>
        		    </tr>
        		<?php endforeach; ?>
        	</table>
        	
        	<?php include(erLhcoreClassDesign::designtpl('lhkernel/paginator.tpl.php')); ?>
            
            <?php else: ?>
                <br>
                <div class="alert alert-info"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','No records were found or search was not executed yet!')?></div>
            <?php endif; ?>
        <?php endif; ?>
        
        
    </div>
</div>