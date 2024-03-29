<div class="col-md-2">
    <div class="form-group">
        <label><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Subject')?></label>
        <?php echo erLhcoreClassRenderHelper::renderMultiDropdown( array (
            'input_name'     => 'subject_id[]',
            'optional_field' => erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Select subject'),
            'selected_id'    => $input->subject_id,
            'css_class'      => 'form-control',
            'display_name'   => 'name',
            'list_function'  => 'erLhAbstractModelSubject::getList',
            'list_function_params'  => array('limit' => false)
        )); ?>
    </div>
</div>