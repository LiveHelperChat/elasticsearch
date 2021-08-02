<h1><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Pending/Active Chats History')?></h1>

<?php if ($pages->items_total > 0): ?>
	<table class="table" ng-non-bindable>
		<thead>
			<tr>
			    <th width="20%"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Chat ID')?></th>
			    <th width="20%"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Chat time')?></th>
			    <th width="20%"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Index time')?></th>
			    <th width="20%"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Department')?></th>
			    <th width="20%"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Status')?></th>
			    <th width="20%"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Indice')?></th>
			    <th></th>
			</tr>  
		</thead>
		<?php foreach ($items as $item) : ?>
		    <tr>
		        <td><?php echo $item->chat_id?></a></td>
		        <td><?php echo $item->time_front?></td>
		        <td><?php echo $item->itime_front?></td>
		        <td><?php echo $item->dep_id?></td>
		        <td><?php echo $item->status?></td>
		        <td><?php echo htmlspecialchars($item->meta_data['index'])?></td>
                <?php if (erLhcoreClassUser::instance()->hasAccessTo('lhelasticsearch','configure')) : ?>
		        <td>
		            <a class="btn btn-danger btn-xs csfr-required" onclick="return confirm('<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('kernel/messages','Are you sure?');?>')" href="<?php echo erLhcoreClassDesign::baseurl('elasticsearch/deletepc')?>/<?php echo htmlspecialchars($item->meta_data['index'])?>/<?php echo $item->id?>"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('user/userlist','Delete');?></a>
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