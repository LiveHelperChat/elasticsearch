<h1><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Edit Vector Document')?></h1>

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
        <small class="text-muted"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','The full original content is shown. On save, it will be re-embedded and may be split into chunks again.')?></small>
    </div>

    <div class="btn-group" role="group">
        <input type="submit" name="Update" value="<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('system/buttons','Save')?>" class="btn btn-primary" />
        <input type="submit" name="Cancel" value="<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('system/buttons','Cancel')?>" class="btn btn-secondary" />
    </div>

    <?php if (!empty($children)) : ?>
    <hr class="mt-4">
    <div class="row mb-3">
        <div class="col-12">
            <h5>
                <i class="material-icons text-info">&#xE3EC;</i>
                <?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Document Chunks')?>
                <span class="badge bg-info ml-2"><?php echo count($children)?></span>
            </h5>
        </div>
    </div>
    <div class="row">
        <?php $chunkNum = 1; foreach ($children as $child) : ?>
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card border-left-info shadow-sm h-100">
                <div class="card-body p-2 px-3" style="font-size:0.9rem">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <span class="badge bg-secondary me-2">#<?php echo $chunkNum?></span>
                        <a class="text-truncate flex-grow-1 font-weight-bold" style="line-height:1.3" href="<?php echo erLhcoreClassDesign::baseurl('elasticsearch/rawvector')?>/<?php echo $child->meta_data['index']?>/<?php echo $child->id?>" target="_blank" title="<?php echo htmlspecialchars($child->name)?>">
                            <?php echo htmlspecialchars($child->name)?>
                        </a>
                        <a class="ms-2 text-muted" href="<?php echo erLhcoreClassDesign::baseurl('elasticsearch/rawvector')?>/<?php echo $child->meta_data['index']?>/<?php echo $child->id?>" target="_blank" title="<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Raw information')?>">
                            <i class="material-icons" style="font-size:1.1rem">&#xE86F;</i>
                        </a>
                    </div>
                    <div class="text-muted small" style="line-height:1.4">
                        <?php $preview = mb_substr(strip_tags($child->content), 0, 120); echo htmlspecialchars($preview); ?><?php echo mb_strlen($child->content) > 120 ? '&hellip;' : ''?>
                    </div>
                </div>
            </div>
        </div>
        <?php $chunkNum++; endforeach; ?>
    </div>
    <?php endif; ?>

</form>
