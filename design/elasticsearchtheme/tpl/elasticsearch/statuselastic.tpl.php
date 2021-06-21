<div class="hide" id="elastic-status-updating">
	<?php $msg = erTranslationClassLhTranslation::getInstance()->getTranslation('system/updateelastic','Updating...'); ?>
	<?php include(erLhcoreClassDesign::designtpl('lhkernel/alert_success.tpl.php')); ?>
</div>

<div class="row" id="elastic-status-checked" ng-non-bindable>
	<div class="col-md-12 form-group">
		
		<h4><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('system/updateelastic','Elastic structure check')?></h4>

		<?php if ($elasticIndexExist == true) : ?>
		
			<?php $hasError = false; ?>
			
			<?php foreach ($typesGeneral as $types) : foreach ($types as $type => $status): ?>
			
				<?php if ($status['error'] == true) : ?>
					<?php $hasError = true; ?>		
					<div class="alert alert-danger"><?php echo $status['status']?></div>
				<?php endif; ?>		
		
			<?php endforeach; endforeach; ?>
			
			<?php if ($hasError): ?>
				<a href="#" class="btn btn-primary" onclick="updateElasticStructure();"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('system/update','Update')?></a>
			<?php else: ?>
				<div data-alert class="alert alert-success"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('system/update','Your elastic does not require any updates')?></div>
			<?php endif; ?>

		<?php else: ?>
		
			<p><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('system/updateelastic','Elastic index not exist'); ?></p>
            <?php if (isset($missingIndexes) && !empty($missingIndexes)) : ?>
            <pre><?php print_r($missingIndexes);?></pre>
            <?php endif;?>
			<a href="#" class="btn btn-primary" onclick="crateElasticIndexs();"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('system/update','Create index')?></a>
					
		<?php endif; ?>
		
	</div>
</div>