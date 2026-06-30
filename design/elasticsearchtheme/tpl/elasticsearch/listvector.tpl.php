<h1><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Vector Storage Documents')?></h1>

<div class="btn-group pull-right" ng-non-bindable>
    <a class="btn btn-primary btn-sm" href="<?php echo erLhcoreClassDesign::baseurl('elasticsearch/newvector')?>"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','New Document')?></a>
</div>

<?php if ($pages->items_total > 0): ?>
    <table class="table table-small table-sm" ng-non-bindable>
        <thead>
        <tr>
            <th width="5%"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','ID')?></th>
            <th width="20%"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Name')?></th>
            <th width="10%"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Department')?></th>
            <th><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Content')?></th>
            <th width="10%"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Chunks')?></th>
            <th width="12%"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Created')?></th>
            <th width="1%"></th>
        </tr>
        </thead>
        <?php foreach ($items as $item) : ?>
            <tr>
                <td nowrap><?php echo $item->id?>
                <?php if (erLhcoreClassUser::instance()->hasAccessTo('lhelasticsearch','configure')) : ?>
                    <a title="<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Raw information')?>" href="<?php echo erLhcoreClassDesign::baseurl('elasticsearch/rawvector')?>/<?php echo $item->meta_data['index']?>/<?php echo $item->id?>"><i class="material-icons">&#xE86F;</i></a>
                <?php endif; ?>
            </td>
                <td><?php echo htmlspecialchars($item->name)?></td>
                <td><?php echo (int)$item->dep_id?></td>
                <td><?php echo nl2br(htmlspecialchars(mb_substr($item->content, 0, 200))) ?><?php echo mb_strlen($item->content) > 200 ? '...' : ''?></td>
                <td>
                    <?php if ($item->children_count > 1) : ?>
                        <span class="badge bg-info"><?php echo (int)$item->children_count?> <?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','chunks')?></span>
                    <?php else : ?>
                        <span class="text-muted">1</span>
                    <?php endif; ?>
                </td>
                <td><?php echo $item->created_at > 0 ? date(erLhcoreClassModule::$dateDateHourFormat, $item->created_at / 1000) : ''?></td>
                <td nowrap="nowrap">
                    <a class="btn btn-secondary btn-xs" href="<?php echo erLhcoreClassDesign::baseurl('elasticsearch/editvector')?>/<?php echo $item->meta_data['index']?>/<?php echo $item->id?>"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('system/buttons','Edit')?></a>
                    <a class="btn btn-danger btn-xs csfr-required" onclick="return confirm('<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Are you sure? This will delete the document and all its chunks.');?>')" href="<?php echo erLhcoreClassDesign::baseurl('elasticsearch/deletevector')?>/<?php echo $item->meta_data['index']?>/<?php echo $item->id?>"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('user/userlist','Delete');?></a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

    <?php include(erLhcoreClassDesign::designtpl('lhkernel/secure_links.tpl.php')); ?>

    <?php include(erLhcoreClassDesign::designtpl('lhkernel/paginator.tpl.php')); ?>

<?php else: ?>
    <p><?=erTranslationClassLhTranslation::getInstance()->getTranslation('system/text','No records found')?></p>
<?php endif; ?>
