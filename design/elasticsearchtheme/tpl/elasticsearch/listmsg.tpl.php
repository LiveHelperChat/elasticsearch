<h1><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Chat messages list')?></h1>

<?php if ($pages->items_total > 0): ?>
	<table class="table" ng-non-bindable>
		<thead>
			<tr>
			    <th width="30%"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Time')?></th>
			    <th width="30%"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Message')?></th>
			    <th width="30%"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','User ID')?></th>
			    <th></th>
			</tr>  
		</thead>
		<?php foreach ($items as $item) : ?>
		    <tr>
		        <td><?php echo date(erLhcoreClassModule::$dateDateHourFormat, $item->time/1000)?></td>
		        <td><?php echo htmlspecialchars($item->msg)?></td>
		        <td><?php echo htmlspecialchars($item->user_id)?></td>
                <?php if (erLhcoreClassUser::instance()->hasAccessTo('lhelasticsearch','configure')) : ?>
		        <td>
		            <a class="btn btn-danger btn-xs csfr-required" onclick="return confirm('<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('kernel/messages','Are you sure?');?>')" href="<?php echo erLhcoreClassDesign::baseurl('elasticsearch/deletemsg')?>/<?php echo $item->chat_id?>/<?php echo $item->id?>"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('user/userlist','Delete');?></a>
		        </td>
                <?php endif;?>
		    </tr>
		<?php endforeach; ?>
	</table>

	<?php include(erLhcoreClassDesign::designtpl('lhkernel/secure_links.tpl.php')); ?>

	<?php include(erLhcoreClassDesign::designtpl('lhkernel/paginator.tpl.php')); ?>

<?php else: ?>
	<p><?=erTranslationClassLhTranslation::getInstance()->getTranslation('system/text','No records found')?></p>
<?php endif; ?>