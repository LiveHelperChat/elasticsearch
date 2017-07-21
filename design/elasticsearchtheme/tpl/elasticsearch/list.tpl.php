<h1><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Chat list')?></h1>

<?php include(erLhcoreClassDesign::designtpl('elasticsearch/parts/filter.tpl.php')); ?>

<?php if ($pages->items_total > 0): ?>
	<table class="table">
		<thead>
			<tr>
			    <th width="30%"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Chat ID')?></th>
			    <th width="30%"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Time')?></th>
			    <th width="30%"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','IP')?></th>
			    <th></th>
			</tr>  
		</thead>
		<?php foreach ($items as $item) : ?>
		    <tr>
		        <td><a href="<?php echo erLhcoreClassDesign::baseurl('elasticsearch/listmsg')?>/<?php echo $item->chat_id?>"><?php echo $item->chat_id?></a></td>
		        <td><?php echo date(erLhcoreClassModule::$dateDateHourFormat, $item->time/1000)?></td>
		        <td><?php echo htmlspecialchars($item->ip)?></td>
		        <td>
		            <a class="btn btn-danger btn-xs csfr-required" onclick="return confirm('<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('kernel/messages','Are you sure?');?>')" href="<?php echo erLhcoreClassDesign::baseurl('elasticsearch/delete')?>/<?php echo $item->id?>"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('user/userlist','Delete');?></a>
		        </td>
		    </tr>
		<?php endforeach; ?>
	</table>

	<?php include(erLhcoreClassDesign::designtpl('lhkernel/secure_links.tpl.php')); ?>

	<?php include(erLhcoreClassDesign::designtpl('lhkernel/paginator.tpl.php')); ?>

<?php else: ?>
	<p><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('system/text','No records found')?></p>
<?php endif; ?>