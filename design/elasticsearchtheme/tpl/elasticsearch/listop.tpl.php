<h1><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Online operators history')?></h1>

<?php if ($pages->items_total > 0): ?>
	<table class="table">
		<thead>
			<tr>
			    <th width="30%"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','User ID')?></th>
			    <th width="30%"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Time')?></th>
			    <th width="30%"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Dep')?></th>
			    <th></th>
			</tr>  
		</thead>
		<?php foreach ($items as $item) : ?>
		    <tr>
		        <td><?php echo $item->user_id?></a></td>
		        <td><?php echo $item->itime_front?></td>
		        <td><?php echo $item->dep_id?></td>
		        <td>
		            <a class="btn btn-danger btn-xs csfr-required" onclick="return confirm('<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('kernel/messages','Are you sure?');?>')" href="<?php echo erLhcoreClassDesign::baseurl('elasticsearch/deleteop')?>/<?php echo $item->id?>"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('user/userlist','Delete');?></a>
		        </td>
		    </tr>
		<?php endforeach; ?>
	</table>

	<?php include(erLhcoreClassDesign::designtpl('lhkernel/secure_links.tpl.php')); ?>

	<?php include(erLhcoreClassDesign::designtpl('lhkernel/paginator.tpl.php')); ?>

<?php else: ?>
	<p><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('system/text','No records found')?></p>
<?php endif; ?>