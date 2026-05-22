<form action="<?php echo erLhcoreClassDesign::baseurl('elasticsearch/listlog')?>" autocomplete="off" method="get" name="SearchFormLog" ng-non-bindable>
    <input type="hidden" name="doSearch" value="1">
    <div class="row">
        <div class="col-md-2">
            <div class="form-group">
                <label><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('bracket/lists/filter','Chat ID');?></label>
                <input type="text" class="form-control" name="chat_id" value="<?php echo htmlspecialchars((string)$input->chat_id)?>" />
            </div>
        </div>
    </div>
    <div class="btn-group" role="group">
        <input type="submit" class="btn btn-secondary" value="<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Search');?>" />
        <?php if ($filterParams['is_search']) : ?>
            <a class="btn btn-secondary" href="<?php echo erLhcoreClassDesign::baseurl('elasticsearch/listlog')?>"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Reset');?></a>
        <?php endif; ?>
    </div>
</form>
