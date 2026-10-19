<h1><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Online Visitors')?></h1>

<?php include(erLhcoreClassDesign::designtpl('elasticsearch/parts/filter_ov.tpl.php')); ?>

<?php if ($pages->items_total > 0): ?>
    <table class="table table-small table-sm online-users-table" ng-non-bindable>
        <thead>
        <tr>
            <th width="1%"></th>
            <th width="1%"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','ID')?></th>
            <th width="19%"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Current Page')?></th>
            <th width="26%"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Highlight')?></th>
            <th width="1%"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Department')?></th>
            <th width="1%"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Indice')?></th>
            <?php if (erLhcoreClassUser::instance()->hasAccessTo('lhelasticsearch','configure')) : ?>
            <th width="1%"></th>
            <?php endif; ?>
        </tr>
        </thead>
        <?php foreach ($items as $item) : ?>
            <tr>
                <td class="text-nowrap">
                    <?php echo $item->lastactivity_ago?> <span class="fs-11"><?php echo $item->time_on_site_front?></span>
                </td>
                <td class="text-nowrap">
                    <a href="#" onclick="lhc.revealModal({'url':WWW_DIR_JAVASCRIPT+'chat/getonlineuserinfo/<?php echo $item->id?>'})"><?php echo $item->nick?></a>
                    <?php if ($item->user_country_code) : ?>
                        <span><img src="<?php echo erLhcoreClassDesign::design('images/flags')?>/<?php echo htmlspecialchars($item->user_country_code)?>.png" alt="<?php echo htmlspecialchars($item->user_country_name)?>" title="<?php echo htmlspecialchars($item->user_country_name . ' (' . $item->city . ' ' . $item->visitor_tz . ' ' .$item->visitor_tz_time)?>)" /></span>
                    <?php endif; ?>
                    <?php if (erLhcoreClassUser::instance()->hasAccessTo('lhelasticsearch','configure')) : ?>
                        <a title="Raw information" href="<?php echo erLhcoreClassDesign::baseurl('elasticsearch/rawov')?>/<?php echo $item->meta_data['index']?>/<?php echo $item->id?>"><i class="material-icons">&#xE86F;</i></a>
                    <?php endif; ?>
                </td>
                <td ng-non-bindable>
                    <?php $currentPageUrl = erLhcoreClassChatValidator::sanitizeUrl($item->current_page);?>
                    <div class="text-truncate" title="<?php echo htmlspecialchars($item->current_page)?>">
                        <?php if ($currentPageUrl != '') : ?>
                            <a target="_blank" rel="noopener" href="<?php echo htmlspecialchars($currentPageUrl)?>"><?php echo erLhcoreClassDesign::shrt($item->current_page, 80, '...', 30);?></a>
                        <?php else : ?>
                            <?php echo erLhcoreClassDesign::shrt($item->current_page, 80, '...', 30);?>
                        <?php endif; ?>
                    </div>
                </td>
                <td ng-non-bindable>
                    <?php if (isset($item->meta_data['highlight']) && is_array($item->meta_data['highlight'])) : ?>
                    <div class="abbr-list-general">
                        <?php foreach ($item->meta_data['highlight'] as $field => $fields) :
                            $highlightText = "➤" . erLhcoreClassBBCode::make_clickable(htmlspecialchars(str_replace(array('<em>','</em>'),array('[mark]','[/mark]'),implode("\n➤", $fields)))); ?>
                            <div>
                                <?php if ($field == 'current_page') : ?>
                                    <i class="fw-bold"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Current Page')?>:</i>
                                <?php elseif ($field == 'page_title') : ?>
                                    <i class="fw-bold"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Page title')?>:</i>
                                <?php else : ?>
                                    <i class="fw-bold"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Referrer')?>:</i>
                                <?php endif;?>
                                <?php echo $highlightText;?></div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </td>
                <td><?php echo $item->dep_id?></td>
                <td nowrap="nowrap"><div class="text-truncate" title="<?php echo htmlspecialchars($item->meta_data['index'])?>"><?php echo htmlspecialchars($item->meta_data['index'])?></div></td>
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