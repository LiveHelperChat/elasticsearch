<h1><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','New Vector Document')?></h1>

<?php if (!empty($errors)) : ?>
<div class="alert alert-danger">
    <ul>
    <?php foreach ($errors as $error) : ?>
        <li><?php echo htmlspecialchars($error)?></li>
    <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<form action="" method="post" ng-non-bindable>
    <?php include(erLhcoreClassDesign::designtpl('lhkernel/csfr_token.tpl.php'));?>

    <div class="form-group">
        <label><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Name')?> *</label>
        <input type="text" class="form-control" name="Name" value="<?php echo htmlspecialchars($item->name)?>" maxlength="250" />
    </div>

    <div class="form-group">
        <label><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Department')?></label>
        <?php echo erLhcoreClassRenderHelper::renderCombobox(array(
            'input_name'     => 'DepId',
            'optional_field' => erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Select department'),
            'display_name'   => 'name',
            'selected_id'    => $item->dep_id,
            'css_class'      => 'form-control',
            'list_function'  => 'erLhcoreClassModelDepartament::getList',
            'list_function_params'  => array('sort' => 'name ASC', 'limit' => false),
        )); ?>
    </div>

    <div class="form-group">
        <label><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Content')?> *</label>
        <textarea class="form-control" style="font-size:13px" rows="12" name="Content" placeholder="<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Enter the document content to embed')?>"><?php echo htmlspecialchars($item->content)?></textarea>
        <small class="text-muted"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Content will be sent to the embed server and split into chunks if too long. Each chunk creates a separate vector document.')?></small>
    </div>

    <div class="btn-group" role="group">
        <input type="submit" name="Update" value="<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('system/buttons','Save')?>" class="btn btn-primary" />
        <input type="submit" name="Cancel" value="<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('system/buttons','Cancel')?>" class="btn btn-secondary" />
    </div>
</form>
