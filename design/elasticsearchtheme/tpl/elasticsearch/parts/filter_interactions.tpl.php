<form action="<?php echo erLhcoreClassDesign::baseurl('elasticsearch/interactions')?>" autocomplete="off" method="get" name="SearchFormRight" ng-non-bindable>

    <input type="hidden" name="ds" value="1">

    <div class="row">
        <div class="col-md-1">
            <div class="form-group">
                <label><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('bracket/lists/filter','Attribute');?></label>
                <select name="attr" class="form-control form-control-sm">
                    <option value="email" <?php ($input->attr == 'email' || $input->attr == '') ? print 'selected' : '' ?> >E-mail</option>
                    <option value="phone" <?php ($input->attr == 'phone') ? print 'selected' : '' ?> >Phone</option>
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
                
                    <button class="btn dropdown-toggle btn-outline-secondary border-secondary-control" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="material-icons">settings</span></button>
                    <div class="dropdown-menu">
                        <label class="dropdown-item mb-0 ps-2"><input type="checkbox" <?php if (is_array($input->search_in) && in_array(2,$input->search_in)) : ?>checked="checked"<?php endif;?> name="search_in[]" value="2" /> Visitor messages</label>
                        <label class="dropdown-item mb-0 ps-2"><input type="checkbox" <?php if (is_array($input->search_in) && in_array(3,$input->search_in)) : ?>checked="checked"<?php endif;?> name="search_in[]" value="3" /> Operator messages</label>
                        <label class="dropdown-item mb-0 ps-2"><input type="checkbox" <?php if (is_array($input->search_in) && in_array(4,$input->search_in)) : ?>checked="checked"<?php endif;?> name="search_in[]" value="4" /> System messages</label>
                        <label class="dropdown-item mb-0 ps-2"><input type="checkbox" <?php if (is_array($input->search_in) && in_array(5,$input->search_in)) : ?>checked="checked"<?php endif;?> name="search_in[]" value="5" /> Subject</label>
                        <label class="dropdown-item mb-0 ps-2"><input type="checkbox" <?php if (is_array($input->search_in) && in_array(6,$input->search_in)) : ?>checked="checked"<?php endif;?> name="search_in[]" value="6" /> Body</label>
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
        <div class="col-md-2">
            <div class="form-group">
                <label><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Date range from');?></label>
                <div class="row">
                    <div class="col-md-12">
                        <input type="text" class="form-control form-control-sm" name="timefrom" id="id_timefrom" placeholder="E.g <?php echo date('Y-m-d',time()-3*31*24*3600)?>" value="<?php echo $input->ds === null ? date('Y-m-d',time()-(3*31*24*3600)) : htmlspecialchars($input->timefrom)?>" />
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="form-group">
                <label><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Hour and minute from');?></label>
                <div class="row">
                    <div class="col-md-6">
                        <select name="timefrom_hours" class="form-control form-control-sm">
                            <option value="">Select hour</option>
                            <?php for ($i = 0; $i <= 23; $i++) : ?>
                                <option value="<?php echo $i?>" <?php if (isset($input->timefrom_hours) && $input->timefrom_hours === $i) : ?>selected="selected"<?php endif;?>><?php echo str_pad($i,2, '0', STR_PAD_LEFT);?> h.</option>
                            <?php endfor;?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <select name="timefrom_minutes" class="form-control form-control-sm">
                            <option value="">Select minute</option>
                            <?php for ($i = 0; $i <= 59; $i++) : ?>
                                <option value="<?php echo $i?>" <?php if (isset($input->timefrom_minutes) && $input->timefrom_minutes === $i) : ?>selected="selected"<?php endif;?>><?php echo str_pad($i,2, '0', STR_PAD_LEFT);?> m.</option>
                            <?php endfor;?>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="form-group">
                <label><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Date range to');?></label>
                <div class="row">
                    <div class="col-md-12">
                        <input type="text" class="form-control form-control-sm" name="timeto" id="id_timeto" placeholder="E.g <?php echo date('Y-m-d')?>" value="<?php echo htmlspecialchars($input->timeto)?>" />
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="form-group">
                <label><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Hour and minute to');?></label>
                <div class="row">
                    <div class="col-md-6">
                        <select name="timeto_hours" class="form-control form-control-sm">
                            <option value="">Select hour</option>
                            <?php for ($i = 0; $i <= 23; $i++) : ?>
                                <option value="<?php echo $i?>" <?php if (isset($input->timeto_hours) && $input->timeto_hours === $i) : ?>selected="selected"<?php endif;?>><?php echo str_pad($i,2, '0', STR_PAD_LEFT);?> h.</option>
                            <?php endfor;?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <select name="timeto_minutes" class="form-control form-control-sm">
                            <option value="">Select minute</option>
                            <?php for ($i = 0; $i <= 59; $i++) : ?>
                                <option value="<?php echo $i?>" <?php if (isset($input->timeto_minutes) && $input->timeto_minutes === $i) : ?>selected="selected"<?php endif;?>><?php echo str_pad($i,2, '0', STR_PAD_LEFT);?> m.</option>
                            <?php endfor;?>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="float-end">
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