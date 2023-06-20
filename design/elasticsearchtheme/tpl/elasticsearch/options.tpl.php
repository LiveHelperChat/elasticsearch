<h1 class="attr-header">Elastic Search Options</h1>

<form action="" method="post" ng-non-bindable>

    <?php include(erLhcoreClassDesign::designtpl('lhkernel/csfr_token.tpl.php'));?>
    
    <?php if (isset($updated) && $updated == 'done') : $msg = erTranslationClassLhTranslation::getInstance()->getTranslation('chat/onlineusers','Settings updated'); ?>
    	<?php include(erLhcoreClassDesign::designtpl('lhkernel/alert_success.tpl.php'));?>
    <?php endif; ?>

    <div class="form-group">
        <label><input type="checkbox" value="on" name="use_es_statistic" <?php isset($es_options['use_es_statistic']) && ($es_options['use_es_statistic'] == true) ? print 'checked="checked"' : ''?> /> Use Elastic Search Statistic</label><br/>
    </div>

    <div class="form-group">
        <label><input type="checkbox" value="on" name="use_es_prev_chats" <?php isset($es_options['use_es_prev_chats']) && ($es_options['use_es_prev_chats'] == true) ? print 'checked="checked"' : ''?> /> Use Elastic Search for previous chats. Search By Username.</label><br/>
    </div>

    <div class="form-group">
        <label><input type="checkbox" value="on" name="use_es_prev_chats_id" <?php isset($es_options['use_es_prev_chats_id']) && ($es_options['use_es_prev_chats_id'] == true) ? print 'checked="checked"' : ''?> /> Use Elastic Search for previous chats. Search By Chat ID.</label><br/>
    </div>

    <div class="form-group">
        <label><input type="checkbox" value="on" name="use_php_resque" <?php isset($es_options['use_php_resque']) && ($es_options['use_php_resque'] == true) ? print 'checked="checked"' : ''?> /> Use PHP Resque for chats indexing</label><br/>
    </div>

    <div class="form-group">
        <label><input type="checkbox" value="on" name="star_month_index" <?php isset($es_options['star_month_index']) && ($es_options['star_month_index'] == true) ? print 'checked="checked"' : ''?> /> Prepend star to month index</label><br/>
    </div>

    <div class="form-group">
        <label><input type="checkbox" value="on" name="check_if_exists" <?php isset($es_options['check_if_exists']) && ($es_options['check_if_exists'] == true) ? print 'checked="checked"' : ''?> /> Check if index exists before saving a chat</label><br/>
    </div>
    
    <div class="form-group">
        <label>Last indexed message Id</label>
        <input type="text" class="form-control" name="last_index_msg_id" value="<?php isset($es_options['last_index_msg_id']) ? print $es_options['last_index_msg_id'] : ''?>" />
    </div>

    <div class="form-group">
        <label>Last indexed participant Id</label>
        <input type="text" class="form-control" name="last_index_part_id" value="<?php isset($es_options['last_index_part_id']) ? print $es_options['last_index_part_id'] : ''?>" />
    </div>

    <div class="form-group">
        <label>Index</label>
        <select name="indexType" class="form-control">
            <option value="static" <?php (isset($es_options['index_type']) && $es_options['index_type'] == 'static') ? print 'selected="selected"' : ''?> >Static</option>
            <option value="daily" <?php (isset($es_options['index_type']) && $es_options['index_type'] == 'daily') ? print 'selected="selected"' : ''?> >Daily</option>
            <option value="monthly" <?php (isset($es_options['index_type']) && $es_options['index_type'] == 'monthly') ? print 'selected="selected"' : ''?> >Monthly</option>
            <option value="yearly" <?php (isset($es_options['index_type']) && $es_options['index_type'] == 'yearly') ? print 'selected="selected"' : ''?> >Yearly</option>
        </select>
    </div>
    <hr>
    <h4>Failover</h4>

    <div class="form-group">
        <label><input type="checkbox" value="on" name="auto_enable" <?php isset($es_options['auto_enable']) && ($es_options['auto_enable'] == true) ? print 'checked="checked"' : ''?> /> Enable elastic search automatically.</label><br/>
    </div>

    <div class="form-group">
        <label><input type="checkbox" value="on" name="disable_es" <?php isset($es_options['disable_es']) && ($es_options['disable_es'] == true) ? print 'checked="checked"' : ''?> /> Disable Elastic Search. Systems set's this automatically if cronjob detects that Elastic Search is down for whatever reason.</label><br/>
    </div>

    <div class="form-group">
        <label><input type="checkbox" value="on" name="disable_es_mail" <?php isset($es_options['disable_es_mail']) && ($es_options['disable_es_mail'] == true) ? print 'checked="checked"' : ''?> /> Disable Elastic Search for mails</label><br/>
    </div>

    <div class="form-group">
        <label>Report unavailable Elastic Search to these e-mails. Separated by comma</label>
        <input type="text" class="form-control" name="report_email_es" value="<?php isset($es_options['report_email_es']) ? print htmlspecialchars($es_options['report_email_es']) : ''?>" />
    </div>

    <?php if (isset($es_options['fail_reason']) && !empty($es_options['fail_reason'])) : ?>
    <p><?php echo htmlspecialchars($es_options['fail_reason'])?></p>
    <?php endif; ?>

    <input type="submit" class="btn btn-secondary" name="StoreOptions" value="<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('system/buttons','Save'); ?>" />

</form>
