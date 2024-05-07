<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header pt-1 pb-1 ps-2 pe-2">
            <h4 class="modal-title" id="myModalLabel"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/expression','Generate expression');?></h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">

            <div role="alert" class="alert alert-success alert-dismissible fade show p-2 ps-2" style="display: none" id="alert-field-success">
                <?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/expression','Updated');?>
            </div>

            <div class="form-group">
                <label><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/expression','Field');?></label>
                <input list="field_list" type="text" name="field" id="field-search" class="form-control form-control-sm" />
            </div>

            <datalist id="field_list" autocomplete="new-password">
                <?php if ($scope == 'chat') : ?>
                    <?php foreach (array_keys((new erLhcoreClassModelESChat())->getState()) as $key) : ?>
                        <option value="<?php echo htmlspecialchars($key)?>"><?php echo htmlspecialchars($key)?></option>
                    <?php endforeach; ?>
                <?php elseif ($scope == 'mail') : ?>
                    <?php foreach (array_keys((new erLhcoreClassModelESMail())->getState()) as $key) : ?>
                        <option value="<?php echo htmlspecialchars($key)?>"><?php echo htmlspecialchars($key)?></option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </datalist>

            <div class="form-group">
                <label><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/expression','Values. One value per row.');?></label>
                <textarea name="values" id="field-values" class="form-control form-control-sm" rows="3"></textarea>
            </div>

            <div class="btn-group">
                <button type="button" id="generate-button" class="btn btn-sm btn-primary"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/expression','Generate and append');?></button>
                <button type="button" id="generate-button-replace" class="btn btn-sm btn-secondary"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/expression','Generate and replace');?></button>
            </div>

            <script>
                $('#generate-button').click(function(){
                    var appendItems = [];
                    $('#field-values').val().split("\n").forEach(function(item){
                        appendItems.push(JSON.stringify(item.trim()));
                    });
                    $('#keyword-field').val(($('#keyword-field').val() != '' ? $('#keyword-field').val() + ' AND ' : '') + $('#field-search').val()+':(' + appendItems.join(' OR ')+')');
                    $('#alert-field-success').show();
                });
                $('#generate-button-replace').click(function(){
                    var appendItems = [];
                    $('#field-values').val().split("\n").forEach(function(item){
                        appendItems.push(JSON.stringify(item.trim()));
                    });
                    $('#keyword-field').val($('#field-search').val()+':(' + appendItems.join(' OR ')+')');
                    $('#alert-field-success').show();
                })
            </script>

<?php include(erLhcoreClassDesign::designtpl('lhkernel/modal_footer.tpl.php'));?>