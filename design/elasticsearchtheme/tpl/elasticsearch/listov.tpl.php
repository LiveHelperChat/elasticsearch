<h1><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Online Visitors')?></h1>

<?php if ($pages->items_total > 0): ?>
    <table class="table table-small table-sm online-users-table" ng-non-bindable>
        <thead>
        <tr>
            <th width="1%"></th>
            <th width="10%"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','ID')?></th>
            <th width="30%"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Current Page')?></th>
            <th width="30%"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Department')?></th>
            <th width="1%"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Indice')?></th>
            <?php if (erLhcoreClassUser::instance()->hasAccessTo('lhelasticsearch','configure')) : ?>
            <th width="1%"></th>
            <?php endif; ?>
        </tr>
        </thead>
        <?php foreach ($items as $item) : ?>
            <tr>
                <td>
                    <?php echo $item->lastactivity_ago?><br/>
                    <span class="fs-11"><?php echo $item->time_on_site_front?></span>
                </td>
                <td>
                    <a href="#" onclick="lhc.revealModal({'url':WWW_DIR_JAVASCRIPT+'chat/getonlineuserinfo/<?php echo $item->id?>'})"><?php echo $item->nick?></a>
                    <?php if ($item->user_country_code) : ?>
                        <span><img src="<?php echo erLhcoreClassDesign::design('images/flags')?>/<?php echo $item->user_country_code?>.png" alt="<?php echo htmlspecialchars($item->user_country_name)?>" title="<?php echo $item->user_country_name . ' (' . $item->city . ' ' . $item->visitor_tz . ' ' .$item->visitor_tz_time?>)" /></span>
                    <?php endif; ?>
                    <?php if (erLhcoreClassUser::instance()->hasAccessTo('lhelasticsearch','configure')) : ?>
                        <a title="Raw information" href="<?php echo erLhcoreClassDesign::baseurl('elasticsearch/rawov')?>/<?php echo $item->meta_data['index']?>/<?php echo $item->id?>"><i class="material-icons">&#xE86F;</i></a>
                    <?php endif; ?>
                </td>
                <td><?php echo $item->current_page?></td>
                <td><?php echo $item->dep_id?></td>
                <td nowrap="nowrap"><?php echo htmlspecialchars($item->meta_data['index'])?></td>
                <?php if (erLhcoreClassUser::instance()->hasAccessTo('lhelasticsearch','configure')) : ?>
                <td><a class="btn btn-danger btn-xs csfr-required" onclick="return confirm('<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('kernel/messages','Are you sure?');?>')" href="<?php echo erLhcoreClassDesign::baseurl('elasticsearch/deleteov')?>/<?php echo htmlspecialchars($item->meta_data['index'])?>/<?php echo $item->id?>"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('user/userlist','Delete');?></a></td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
    </table>

    <?php include(erLhcoreClassDesign::designtpl('lhkernel/secure_links.tpl.php')); ?>

    <?php include(erLhcoreClassDesign::designtpl('lhkernel/paginator.tpl.php')); ?>

<?php else: ?>
    <p><?=erTranslationClassLhTranslation::getInstance()->getTranslation('system/text','No records found')?></p>
<?php endif; ?>