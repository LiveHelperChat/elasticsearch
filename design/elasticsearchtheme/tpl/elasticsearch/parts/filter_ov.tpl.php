<form action="<?php echo erLhcoreClassDesign::baseurl('elasticsearch/listov')?>" autocomplete="off" method="get" name="SearchFormOV" ng-non-bindable>
    <input type="hidden" name="ds" value="1">
    <div class="row">

        <div class="col-md-3">
            <div class="form-group">
                <label><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Keyword');?></label>
                <div class="input-group input-group-sm">
                    <input type="text" placeholder="<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Keyword');?>" class="form-control form-control-sm" name="keyword" id="keyword-field" value="<?php echo htmlspecialchars((string)$input->keyword)?>" />
                    <button class="btn dropdown-toggle btn-outline-secondary border-secondary-control" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="material-icons">settings</span></button>
                    <div class="dropdown-menu">
                        <label class="dropdown-item mb-0 ps-2"><input type="checkbox" <?php if ($input->expression == true) : ?>checked="checked"<?php endif;?> name="expression" value="on" /> Expression. Below options becomes irrelevant</label>
                        <div role="separator" class="dropdown-divider"></div>
                        <label class="dropdown-item mb-0 ps-2"><input type="checkbox" <?php if (is_array($input->search_in) && in_array(1,$input->search_in)) : ?>checked="checked"<?php endif;?> name="search_in[]" value="1" /> Current page</label>
                        <label class="dropdown-item mb-0 ps-2"><input type="checkbox" <?php if (is_array($input->search_in) && in_array(2,$input->search_in)) : ?>checked="checked"<?php endif;?> name="search_in[]" value="2" /> Page title</label>
                        <label class="dropdown-item mb-0 ps-2"><input type="checkbox" <?php if (is_array($input->search_in) && in_array(3,$input->search_in)) : ?>checked="checked"<?php endif;?> name="search_in[]" value="3" /> Referrer</label>
                        <div role="separator" class="dropdown-divider"></div>
                        <label class="dropdown-item mb-0 ps-2"><input type="checkbox" <?php if ($input->exact_match == true) : ?>checked="checked"<?php endif;?> name="exact_match" value="on" /> Exact match phrase</label>
                        <label class="dropdown-item mb-0 ps-2"><input type="checkbox" <?php if ($input->fuzzy == true) : ?>checked="checked"<?php endif;?> name="fuzzy" value="on" /> Fuzzy search, allow typos</label>

                        <label class="dropdown-item mb-0 ps-2"><input type="radio" <?php if ($input->fuzzy_prefix == 1) : ?>checked="checked"<?php endif;?> name="fuzzy_prefix" value="1" /> Length of the keyword minus 1 character (default)</label>
                        <label class="dropdown-item mb-0 ps-2"><input type="radio" <?php if ($input->fuzzy_prefix == 2) : ?>checked="checked"<?php endif;?> name="fuzzy_prefix" value="2" /> Length of the keyword minus 2 character's</label>
                        <label class="dropdown-item mb-0 ps-2"><input type="radio" <?php if ($input->fuzzy_prefix == 3) : ?>checked="checked"<?php endif;?> name="fuzzy_prefix" value="3" /> Length of the keyword minus 3 character's</label>
                        <label class="dropdown-item mb-0 ps-2"><input type="radio" <?php if ($input->fuzzy_prefix == 4) : ?>checked="checked"<?php endif;?> name="fuzzy_prefix" value="4" /> Length of the keyword minus 4 character's</label>
                        <label class="dropdown-item mb-0 ps-2"><input type="radio" <?php if ($input->fuzzy_prefix == 5) : ?>checked="checked"<?php endif;?> name="fuzzy_prefix" value="5" /> Length of the keyword minus 5 character's</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Department');?></label>
                <?php echo erLhcoreClassRenderHelper::renderMultiDropdown( array (
                    'input_name'     => 'department_ids[]',
                    'optional_field' => erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Choose department'),
                    'selected_id'    => $input->department_ids,
                    'css_class'      => 'form-control',
                    'display_name'   => 'name',
                    'ajax'           => 'deps',
                    'list_function'  => 'erLhcoreClassModelDepartament::getList',
                    'list_function_params'  => array('sort' => '`name` ASC', 'limit' => 20)
                )); ?>
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('bracket/lists/filter','IP');?></label>
                <input type="text" class="form-control form-control-sm" name="ip" placeholder="E.g 192.168.1.1" value="<?php echo htmlspecialchars((string)$input->ip)?>" />
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <label><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Date range from');?> (<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Last visit');?>)</label>
                <input type="text" class="form-control form-control-sm" name="timefrom" id="id_timefrom" placeholder="E.g <?php echo date('Y-m-d',time()-3*31*24*3600)?>" value="<?php echo htmlspecialchars((string)$input->timefrom)?>" />
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Hour and minute from');?></label>
                <div class="row">
                    <div class="col-md-4">
                        <select name="timefrom_hours" class="form-control form-control-sm">
                            <option value=""><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Select hour');?></option>
                            <?php for ($i = 0; $i <= 23; $i++) : ?>
                                <option value="<?php echo $i?>" <?php if (isset($input->timefrom_hours) && $input->timefrom_hours === $i) : ?>selected="selected"<?php endif;?>><?php echo str_pad($i,2, '0', STR_PAD_LEFT);?> h.</option>
                            <?php endfor;?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select name="timefrom_minutes" class="form-control form-control-sm">
                            <option value=""><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Select minute');?></option>
                            <?php for ($i = 0; $i <= 59; $i++) : ?>
                                <option value="<?php echo $i?>" <?php if (isset($input->timefrom_minutes) && $input->timefrom_minutes === $i) : ?>selected="selected"<?php endif;?>><?php echo str_pad($i,2, '0', STR_PAD_LEFT);?> m.</option>
                            <?php endfor;?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select name="timefrom_seconds" class="form-control form-control-sm">
                            <option value=""><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Select seconds');?></option>
                            <?php for ($i = 0; $i <= 59; $i++) : ?>
                                <option value="<?php echo $i?>" <?php if (isset($input->timefrom_seconds) && $input->timefrom_seconds === $i) : ?>selected="selected"<?php endif;?>><?php echo str_pad($i,2, '0', STR_PAD_LEFT);?> s.</option>
                            <?php endfor;?>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Date range to');?></label>
                <input type="text" class="form-control form-control-sm" name="timeto" id="id_timeto" placeholder="E.g <?php echo date('Y-m-d')?>" value="<?php echo htmlspecialchars((string)$input->timeto)?>" />
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Hour and minute to');?></label>
                <div class="row">
                    <div class="col-md-4">
                        <select name="timeto_hours" class="form-control form-control-sm">
                            <option value=""><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Select hour');?></option>
                            <?php for ($i = 0; $i <= 23; $i++) : ?>
                                <option value="<?php echo $i?>" <?php if (isset($input->timeto_hours) && $input->timeto_hours === $i) : ?>selected="selected"<?php endif;?>><?php echo str_pad($i,2, '0', STR_PAD_LEFT);?> h.</option>
                            <?php endfor;?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select name="timeto_minutes" class="form-control form-control-sm">
                            <option value=""><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Select minute');?></option>
                            <?php for ($i = 0; $i <= 59; $i++) : ?>
                                <option value="<?php echo $i?>" <?php if (isset($input->timeto_minutes) && $input->timeto_minutes === $i) : ?>selected="selected"<?php endif;?>><?php echo str_pad($i,2, '0', STR_PAD_LEFT);?> m.</option>
                            <?php endfor;?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select name="timeto_seconds" class="form-control form-control-sm">
                            <option value=""><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Select seconds');?></option>
                            <?php for ($i = 0; $i <= 59; $i++) : ?>
                                <option value="<?php echo $i?>" <?php if (isset($input->timeto_seconds) && $input->timeto_seconds === $i) : ?>selected="selected"<?php endif;?>><?php echo str_pad($i,2, '0', STR_PAD_LEFT);?> s.</option>
                            <?php endfor;?>
                        </select>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="btn-group pb-2" role="group" aria-label="...">
        <input type="submit" name="doSearchSubmit" class="btn btn-primary btn-sm" value="<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Search');?>" />

        <select name="sort_ov" class="form-control-sm rounded-0 border-secondary border-end-0" onchange="this.form.submit()">
            <option value="last_visit_desc" <?php if ($input->sort_ov == 'last_visit_desc' || $input->sort_ov == '') : ?>selected="selected"<?php endif; ?>><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Last visit from new to old');?></option>
            <option value="last_visit_asc" <?php if ($input->sort_ov == 'last_visit_asc') : ?>selected="selected"<?php endif; ?>><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Last visit from old to new');?></option>
            <option value="first_visit_desc" <?php if ($input->sort_ov == 'first_visit_desc') : ?>selected="selected"<?php endif; ?>><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','First visit from new to old');?></option>
            <option value="first_visit_asc" <?php if ($input->sort_ov == 'first_visit_asc') : ?>selected="selected"<?php endif; ?>><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','First visit from old to new');?></option>
            <option value="time_on_site_desc" <?php if ($input->sort_ov == 'time_on_site_desc') : ?>selected="selected"<?php endif; ?>><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Time on site high to low');?></option>
            <option value="pages_count_desc" <?php if ($input->sort_ov == 'pages_count_desc') : ?>selected="selected"<?php endif; ?>><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Pages count high to low');?></option>
            <option value="total_visits_desc" <?php if ($input->sort_ov == 'total_visits_desc') : ?>selected="selected"<?php endif; ?>><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Total visits high to low');?></option>
            <option value="relevance" <?php if ($input->sort_ov == 'relevance') : ?>selected="selected"<?php endif; ?>><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Relevance');?></option>
        </select>

        <a href="<?php echo erLhcoreClassDesign::baseurl('elasticsearch/listov')?>" class="btn btn-outline-secondary btn-sm"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Reset');?></a>
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
