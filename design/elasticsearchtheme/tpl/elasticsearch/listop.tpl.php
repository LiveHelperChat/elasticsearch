<h1><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Online operators history')?></h1>

<?php if ($pages->items_total > 0): ?>
	<table class="table" ng-non-bindable>
		<thead>
			<tr>
			    <th width="30%"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','User ID')?></th>
			    <th width="30%"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Time')?></th>
			    <th width="30%"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Dep')?></th>
			    <th width="30%" nowrap=""><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Free slots')?></th>
			    <th width="30%" nowrap=""><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Total slots')?></th>
			    <th width="30%" nowrap=""><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Active chats')?></th>
			    <th width="30%" nowrap=""><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Pending chats')?></th>
			    <th width="30%" nowrap=""><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Inactive chats')?></th>
			    <th width="30%"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Indice')?></th>
			    <th></th>
			</tr>  
		</thead>
		<?php foreach ($items as $item) : ?>
		    <tr>
		        <td><?php echo $item->user_id?></a></td>
		        <td><?php echo $item->itime_front?></td>
		        <td><?php echo json_encode($item->dep_ids,true)?></td>
		        <td><?php echo $item->free_slots?></td>
		        <td><?php echo $item->max_chats?></td>
		        <td><?php echo $item->active_chats?></td>
		        <td><?php echo $item->pending_chats?></td>
		        <td><?php echo $item->inactive_chats?></td>
                <td><?php echo htmlspecialchars($item->meta_data['index'])?></td>
                <?php if (erLhcoreClassUser::instance()->hasAccessTo('lhelasticsearch','configure')) : ?>
		        <td>
		            <a class="btn btn-danger btn-xs csfr-required" onclick="return confirm('<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('kernel/messages','Are you sure?');?>')" href="<?php echo erLhcoreClassDesign::baseurl('elasticsearch/deleteop')?>/<?php echo htmlspecialchars($item->meta_data['index'])?>/<?php echo $item->id?>"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('user/userlist','Delete');?></a>
		        </td>
                <?php endif; ?>
		    </tr>
		<?php endforeach; ?>
	</table>



	<?php include(erLhcoreClassDesign::designtpl('lhkernel/secure_links.tpl.php')); ?>

	<?php include(erLhcoreClassDesign::designtpl('lhkernel/paginator.tpl.php')); ?>

<?php else: ?>
	<p><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('system/text','No records found')?></p>
<?php endif; ?>