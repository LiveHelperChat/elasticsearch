<div class="col-12">
    <div class="row">
        <div class="col-12">
            <label><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Subject')?></label>
            <?php echo erLhcoreClassRenderHelper::renderMultiDropdown( array (
                'input_name'     => 'subject_ids[]',
                'optional_field' => erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Select subject'),
                'selected_id'    => $input->subject_ids,
                'css_class'      => 'form-control',
                'display_name'   => 'name',
                'list_function_params' => [],
                'list_function'  => 'erLhAbstractModelSubject::getList'
            )); ?>
        </div>
    </div>
</div>
