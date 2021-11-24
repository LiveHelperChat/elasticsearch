<form action="<?php echo erLhcoreClassDesign::baseurl('elasticsearch/interactions')?>" autocomplete="off" method="get" name="SearchFormRight" ng-non-bindable>

    <input type="hidden" name="ds" value="1">

    <div class="row">
        <div class="col-md-1">
            <div class="form-group">
                <label><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('bracket/lists/filter','Attribute');?></label>
                <select name="attr" class="form-control form-control-sm">
                    <option value="email" <?php ($input->attr == 'email' || $input->attr == '') ? print 'selected' : '' ?> >E-mail</option>
                </select>
            </div>
        </div>
        <div class="col-md-1">
            <div class="form-group">
                <label><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('bracket/lists/filter','Attribute value');?></label>
                <input type="text" class="form-control form-control-sm" value="<?php echo htmlspecialchars($input->val)?>" name="val">
            </div>
        </div>
        <div class="col-md-2">
            <label><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('bracket/lists/filter','Keyword messages');?></label>
            <div class="input-group input-group-sm">
                <input type="text" placeholder="<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('bracket/lists/filter','Keyword messages');?>" class="form-control form-control-sm" name="keyword" value="<?php echo htmlspecialchars($input->keyword)?>" />
                <div class="input-group-append ">
                    <button class="btn dropdown-toggle btn-outline-secondary border-secondary-control" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="material-icons">settings</span></button>
                    <div class="dropdown-menu">
                        <label class="dropdown-item mb-0 pl-2"><input type="checkbox" <?php if (is_array($input->search_in) && in_array(2,$input->search_in)) : ?>checked="checked"<?php endif;?> name="search_in[]" value="2" /> Visitor messages</label>
                        <label class="dropdown-item mb-0 pl-2"><input type="checkbox" <?php if (is_array($input->search_in) && in_array(3,$input->search_in)) : ?>checked="checked"<?php endif;?> name="search_in[]" value="3" /> Operator messages</label>
                        <label class="dropdown-item mb-0 pl-2"><input type="checkbox" <?php if (is_array($input->search_in) && in_array(4,$input->search_in)) : ?>checked="checked"<?php endif;?> name="search_in[]" value="4" /> System messages</label>
                        <label class="dropdown-item mb-0 pl-2"><input type="checkbox" <?php if (is_array($input->search_in) && in_array(5,$input->search_in)) : ?>checked="checked"<?php endif;?> name="search_in[]" value="5" /> Subject</label>
                        <label class="dropdown-item mb-0 pl-2"><input type="checkbox" <?php if (is_array($input->search_in) && in_array(6,$input->search_in)) : ?>checked="checked"<?php endif;?> name="search_in[]" value="6" /> Body</label>
                        <div role="separator" class="dropdown-divider"></div>
                        <label class="dropdown-item mb-0 pl-2"><input type="checkbox" <?php if ($input->exact_match == true) : ?>checked="checked"<?php endif;?> name="exact_match" value="on" /> Exact match phrase</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="float-right">
        <?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Records in total');?> - <?php echo $total_literal;?>
    </div>

    <div class="btn-group" role="group" aria-label="...">
        <input type="submit" name="doSearchSubmit" class="btn btn-primary btn-sm" value="<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Search');?>" />
        <a class="btn btn-outline-secondary btn-sm" href="<?php echo erLhcoreClassDesign::baseurl('elasticsearch/interactions')?>"><span class="material-icons">refresh</span><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Reset');?></a>
    </div>

</form>
<script>
    $(function() {
        $('#id_timefrom,#id_timeto').fdatepicker({
            format: 'yyyy-mm-dd'
        });
        $('.btn-block-department').makeDropdown();
    });
</script>