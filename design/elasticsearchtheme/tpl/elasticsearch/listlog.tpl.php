<h1><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','REST API Log')?></h1>

<?php include(erLhcoreClassDesign::designtpl('elasticsearch/parts/filter_log.tpl.php')); ?>

<?php if ($pages->items_total > 0): ?>
    <table class="table table-small table-sm" ng-non-bindable>
        <thead>
        <tr>
            <th width="5%"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','ID')?></th>
            <th width="15%"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Time')?></th>
            <th width="10%"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Chat ID')?></th>
            <th><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Message')?></th>
            <?php if (erLhcoreClassUser::instance()->hasAccessTo('lhelasticsearch','configure')) : ?>
            <th width="1%"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Indice')?></th>
            <th width="1%"></th>
            <?php endif; ?>
        </tr>
        </thead>
        <?php foreach ($items as $item) : ?>
            <tr>
                <td>
                    <?php echo (int)$item->id?>
                    <?php if (erLhcoreClassUser::instance()->hasAccessTo('lhelasticsearch','configure')) : ?>
                        <a title="<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Raw information')?>" href="<?php echo erLhcoreClassDesign::baseurl('elasticsearch/rawlog')?>/<?php echo $item->meta_data['index']?>/<?php echo $item->id?>"><i class="material-icons">&#xE86F;</i></a>
                    <?php endif; ?>
                </td>
                <td><?php echo date(erLhcoreClassModule::$dateDateHourFormat, $item->time / 1000)?></td>
                <td>
                    <?php if ($item->chat_id): ?>
                        <a href="<?php echo erLhcoreClassDesign::baseurl('chat/single')?>/<?php echo (int)$item->chat_id?>"><?php echo (int)$item->chat_id?></a>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
                <td><?php echo nl2br(htmlspecialchars($item->msg))?></td>
                <?php if (erLhcoreClassUser::instance()->hasAccessTo('lhelasticsearch','configure')) : ?>
                <td nowrap="nowrap"><?php echo htmlspecialchars($item->meta_data['index'])?></td>
                <td><a class="btn btn-danger btn-xs csfr-required" onclick="return confirm('<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('kernel/messages','Are you sure?');?>')" href="<?php echo erLhcoreClassDesign::baseurl('elasticsearch/deletelog')?>/<?php echo htmlspecialchars($item->meta_data['index'])?>/<?php echo $item->id?>"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('user/userlist','Delete');?></a></td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
    </table>

    <?php include(erLhcoreClassDesign::designtpl('lhkernel/secure_links.tpl.php')); ?>

    <?php include(erLhcoreClassDesign::designtpl('lhkernel/paginator.tpl.php')); ?>

<?php else: ?>
    <p><?=erTranslationClassLhTranslation::getInstance()->getTranslation('system/text','No records found')?></p>
<?php endif; ?>
