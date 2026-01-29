<h1 class="attr-header">Re-index scheduler</h1>


<?php
$db = ezcDbInstance::get();
$presentChats = (int)$db->query('SELECT COUNT(*) FROM `lhc_lheschat_index`')->fetchColumn();
$presentMails = (int)$db->query('SELECT COUNT(*) FROM `lhc_lhesmail_index`')->fetchColumn();
?>

<ul>
    <li>Pending chats re-index - <?php echo $presentChats; ?></li>
    <li>Pending mails re-index - <?php echo $presentMails; ?></li>
</ul>

<form action="" method="post" ng-non-bindable>

    <?php include(erLhcoreClassDesign::designtpl('lhkernel/csfr_token.tpl.php'));?>

    <?php if (isset($updated) && $updated == 'done') : $msg = erTranslationClassLhTranslation::getInstance()->getTranslation('chat/onlineusers','Rescheduled').' - '. $affected . ' records'; ?>
        <?php include(erLhcoreClassDesign::designtpl('lhkernel/alert_success.tpl.php'));?>
    <?php endif; ?>

    <div class="form-group">
        <label>Last n hours to re-index. Max 10000.</label>
        <input type="number" min="1" max="10000" class="form-control" name="hours" value="<?php isset($_POST['hours']) ? print (int)$_POST['hours'] : print '1'; ?>" />
    </div>

    <div class="form-group">
        <label>Type</label>
        <select name="type" class="form-control">
            <option value="0" <?php (isset($_POST['type']) && $_POST['type'] == 0) ? print 'selected="selected"' : ''?> >Chats</option>
            <option value="1" <?php (isset($_POST['type']) && $_POST['type'] == 1) ? print 'selected="selected"' : ''?> >Mails</option>
        </select>
    </div>

    <input type="submit" class="btn btn-secondary" name="reindexAction" value="<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('system/buttons','Schedule re-index'); ?>" />

</form>
