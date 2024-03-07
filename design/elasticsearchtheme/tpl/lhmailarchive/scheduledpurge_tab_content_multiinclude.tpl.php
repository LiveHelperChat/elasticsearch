<div role="tabpanel" class="tab-pane" id="elastic">

<?php $items = \LiveHelperChatExtension\elasticsearch\providers\Delete\DeleteFilter::getList(array('offset' => 0, 'limit' => 1000, 'sort' => 'id ASC')); ?>

<table ng-non-bindable class="table table-sm" cellpadding="0" cellspacing="0">
    <thead>
    <tr>
        <th width="1%">ID</th>
        <th><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chatarchive/list','User ID');?></th>
        <th><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chatarchive/list','Archive ID');?></th>
        <th><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chatarchive/list','Status');?></th>
        <th><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chatarchive/list','Created At');?></th>
        <th><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chatarchive/list','Updated At');?></th>
        <th><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chatarchive/list','Started At');?></th>
        <th><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chatarchive/list','Finished At');?></th>
        <th><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chatarchive/list','Filter');?></th>
        <th><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chatarchive/list','Pending records to process');?></th>
        <th><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chatarchive/list','Last ID');?></th>
    </tr>
    </thead>
    <?php include(erLhcoreClassDesign::designtpl('lhmailarchive/scheduledpurge_table_content.tpl.php'));?>
</table>

</div>