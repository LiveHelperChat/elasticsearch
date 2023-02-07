
<form action="<?php echo erLhcoreClassDesign::baseurl('elasticsearch/listmail')?>" autocomplete="off" method="get" name="SearchFormRight" ng-non-bindable>
    <input type="hidden" name="ds" value="1">
    <div class="row">

        <div class="col-md-1">
            <div class="form-group">
                <label><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('bracket/lists/filter','Conversation ID');?></label>
                <input type="text" class="form-control form-control-sm" placeholder="<?php echo htmlspecialchars("<id>[,<id>]");?>" name="conversation_id" value="<?php echo htmlspecialchars($input->conversation_id)?>" />
            </div>
        </div>

        <div class="col-md-2">
            <label><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('bracket/lists/filter','Keyword messages');?></label>
            <div class="input-group input-group-sm">
                <input type="text" placeholder="<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('bracket/lists/filter','Search in Subject or Body or selected fields');?>" class="form-control form-control-sm" name="keyword" value="<?php echo htmlspecialchars($input->keyword)?>" />
                
                    <button class="btn dropdown-toggle btn-outline-secondary border-secondary-control" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="material-icons">settings</span></button>
                    <div class="dropdown-menu">
                        <label class="dropdown-item mb-0 ps-2"><input type="checkbox" <?php if (is_array($input->search_in) && in_array(1,$input->search_in)) : ?>checked="checked"<?php endif;?> name="search_in[]" value="1" /> Subject</label>
                        <label class="dropdown-item mb-0 ps-2"><input type="checkbox" <?php if (is_array($input->search_in) && in_array(2,$input->search_in)) : ?>checked="checked"<?php endif;?> name="search_in[]" value="2" /> Body</label>
                        <label class="dropdown-item mb-0 ps-2"><input type="checkbox" <?php if (is_array($input->search_in) && in_array(3,$input->search_in)) : ?>checked="checked"<?php endif;?> name="search_in[]" value="3" /> From name</label>
                        <label class="dropdown-item mb-0 ps-2"><input type="checkbox" <?php if (is_array($input->search_in) && in_array(4,$input->search_in)) : ?>checked="checked"<?php endif;?> name="search_in[]" value="4" /> Sender name</label>
                        <label class="dropdown-item mb-0 ps-2"><input type="checkbox" <?php if (is_array($input->search_in) && in_array(12,$input->search_in)) : ?>checked="checked"<?php endif;?> name="search_in[]" value="12" /> Customer name</label>
                        <label class="dropdown-item mb-0 ps-2"><input type="checkbox" <?php if (is_array($input->search_in) && in_array(5,$input->search_in)) : ?>checked="checked"<?php endif;?> name="search_in[]" value="5" /> Delivery status</label>
                        <label class="dropdown-item mb-0 ps-2"><input type="checkbox" <?php if (is_array($input->search_in) && in_array(6,$input->search_in)) : ?>checked="checked"<?php endif;?> name="search_in[]" value="6" /> Undelivered mail body</label>
                        <label class="dropdown-item mb-0 ps-2"><input type="checkbox" <?php if (is_array($input->search_in) && in_array(7,$input->search_in)) : ?>checked="checked"<?php endif;?> name="search_in[]" value="7" /> In Reply to data</label>
                        <label class="dropdown-item mb-0 ps-2"><input type="checkbox" <?php if (is_array($input->search_in) && in_array(8,$input->search_in)) : ?>checked="checked"<?php endif;?> name="search_in[]" value="8" /> In To data</label>
                        <label class="dropdown-item mb-0 ps-2"><input type="checkbox" <?php if (is_array($input->search_in) && in_array(9,$input->search_in)) : ?>checked="checked"<?php endif;?> name="search_in[]" value="9" /> In CC data</label>
                        <label class="dropdown-item mb-0 ps-2"><input type="checkbox" <?php if (is_array($input->search_in) && in_array(10,$input->search_in)) : ?>checked="checked"<?php endif;?> name="search_in[]" value="10" /> In BCC data</label>
                        <label class="dropdown-item mb-0 ps-2"><input type="checkbox" <?php if (is_array($input->search_in) && in_array(11,$input->search_in)) : ?>checked="checked"<?php endif;?> name="search_in[]" value="11" /> In mailbox path</label>
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

        <div class="col-md-1">
            <div class="form-group">
                <label><?php include(erLhcoreClassDesign::designtpl('elasticsearch/parts/nick_title.tpl.php')); ?></label>
                <input type="text" class="form-control form-control-sm" name="from_name" value="<?php echo htmlspecialchars($input->from_name)?>" />
            </div>
        </div>

        <div class="col-md-1">
            <label><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','E-mail');?></label>
            <div class="input-group input-group-sm">
                <input type="text" placeholder="<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('bracket/lists/filter','E-mail');?>" class="form-control form-control-sm" name="email" value="<?php echo htmlspecialchars($input->email)?>" />

                    <button class="btn dropdown-toggle btn-outline-secondary border-secondary-control" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="material-icons">settings</span></button>
                    <div class="dropdown-menu">
                        <label class="dropdown-item mb-0 ps-2"><input type="radio" <?php if (empty($input->search_email_in) || $input->search_email_in == 1) : ?>checked="checked"<?php endif;?> name="search_email_in" value="1" /> Customer e-mail</label>
                        <label class="dropdown-item mb-0 ps-2"><input type="radio" <?php if ($input->search_email_in == 2) : ?>checked="checked"<?php endif;?> name="search_email_in" value="2" /> Sender e-mail</label>
                    </div>

            </div>
        </div>

        <div class="col-md-2">
            <div class="form-group">
                <label><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Mailbox');?></label>
                <?php echo erLhcoreClassRenderHelper::renderMultiDropdown( array (
                    'input_name'     => 'mailbox_ids[]',
                    'optional_field' => erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Choose mailbox'),
                    'selected_id'    => $input->mailbox_ids,
                    'css_class'      => 'form-control',
                    'display_name'   => 'mail',
                    'list_function_params' => ['limit' => false, 'sort' => '`mail` ASC'],
                    'list_function'  => 'erLhcoreClassModelMailconvMailbox::getList'
                )); ?>
            </div>
        </div>


        <div class="col-md-1">
            <div class="form-group">
                <?php include(erLhcoreClassDesign::designtpl('elasticsearch/parts/user_title.tpl.php')); ?>
                <label><?php echo $userTitle['user'];?></label>
                <?php echo erLhcoreClassRenderHelper::renderMultiDropdown( array (
                    'input_name'     => 'user_ids[]',
                    'optional_field' => $userTitle['user_select'],
                    'selected_id'    => $input->user_ids,
                    'css_class'      => 'form-control',
                    'display_name'   => 'name_official',
                    'ajax'           => 'users',
                    'list_function'  => 'erLhcoreClassModelUser::getUserList',
                    'list_function_params'  => array('limit' => 50, 'sort' => '`name` ASC')
                )); ?>
            </div>
        </div>

        <div class="col-md-3">
            <div class="row">
                <div class="col-6">
                    <div class="form-group">
                        <label><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Department');?></label>
                        <?php echo erLhcoreClassRenderHelper::renderMultiDropdown( array (
                            'input_name'     => 'department_ids[]',
                            'optional_field' => erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Choose department'),
                            'selected_id'    => $input->department_ids,
                            'css_class'      => 'form-control',
                            'display_name'   => 'name',
                            'list_function'  => 'erLhcoreClassModelDepartament::getList',
                            'ajax'           => 'deps',
                            'list_function_params' => array_merge(['sort' => '`name` ASC', 'limit' => 20],erLhcoreClassUserDep::conditionalDepartmentFilter()),
                        )); ?>
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <label><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Department group');?></label>
                        <?php echo erLhcoreClassRenderHelper::renderMultiDropdown( array (
                            'input_name'     => 'department_group_ids[]',
                            'optional_field' => erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Choose department group'),
                            'selected_id'    => $input->department_group_ids,
                            'css_class'      => 'form-control',
                            'display_name'   => 'name',
                            'list_function'  => 'erLhcoreClassModelDepartamentGroup::getList',
                            'list_function_params' => array_merge(['sort' => '`name` ASC','limit' => false],erLhcoreClassUserDep::conditionalDepartmentGroupFilter()),
                        )); ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-1">
            <div class="form-group">
                <label><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Subject')?></label>
                <?php echo erLhcoreClassRenderHelper::renderMultiDropdown( array (
                    'input_name'     => 'subject_id[]',
                    'optional_field' => erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Select subject'),
                    'selected_id'    => $input->subject_id,
                    'css_class'      => 'form-control',
                    'display_name'   => 'name',
                    'list_function'  => 'erLhAbstractModelSubject::getList',
                    'list_function_params'  => array('limit' => false,'sort' => '`name` ASC')
                )); ?>
            </div>
        </div>
    </div>

    <div class="row">

        <div class="col-12 pb-2">
            <a href="#" onclick="$('#advanced-search').toggle()"><span class="material-icons">search</span>&nbsp;<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Advanced search');?></a>
        </div>

        <div class="col-12" id="advanced-search" style="display: none">

            <hr class="mt-0">

            <div class="row">

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

                <div class="col-md-2">
                    <div class="form-group">
                        <label><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','User group');?></label>
                        <?php echo erLhcoreClassRenderHelper::renderMultiDropdown( array (
                            'input_name'     => 'group_ids[]',
                            'optional_field' => erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Select group'),
                            'selected_id'    => $input->group_ids,
                            'css_class'      => 'form-control',
                            'display_name'   => 'name',
                            'list_function'  => 'erLhcoreClassModelGroup::getList',
                            'list_function_params' => array_merge(['sort' => '`name` ASC', 'limit' => false],erLhcoreClassUserDep::conditionalDepartmentGroupFilter()),
                        )); ?>

                    </div>
                </div>
                
                <div class="col-md-2">
                    <label><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Open status');?></label>
                    <select name="opened" class="form-control form-control-sm">
                        <option value=""><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Any')?></option>
                        <option value="0" <?php if ($input->opened === 0) : ?>selected="selected"<?php endif;?> ><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Not opened')?></option>
                        <option value="1" <?php if ($input->opened === 1) : ?>selected="selected"<?php endif;?> ><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Opened')?></option>
                    </select>
                </div>


                <div class="col-12">
                    <div class="row">

                        <div class="col-md-2">
                            <div class="form-group">
                                <label><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('bracket/lists/filter','Sort');?></label>
                                <select name="sort_chat" class="form-control form-control-sm">
                                    <option value="desc" <?php ($input->sort_chat == 'desc' || $input->sort_chat == '') ? print 'selected="selected"' : null?> >From new to old</option>
                                    <option value="asc" <?php $input->sort_chat == 'asc' ? print 'selected="selected"' : null?> >From old to new</option>
                                    <option value="relevance" <?php ($input->sort_chat == 'relevance') ? print 'selected="selected"' : null?> >Relevance</option>
                                    <option value="lastupdatedesc" <?php if ($input->sort_chat == 'lastupdatedesc') : ?>selected="selected"<?php endif; ?> ><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('pagelayout/pagelayout','Newest replies first');?></option>
                                    <option value="lastupdateasc" <?php if ($input->sort_chat == 'lastupdateasc') : ?>selected="selected"<?php endif; ?> ><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('pagelayout/pagelayout','Oldest replies first');?></option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="form-group">
                                <label><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('bracket/lists/filter','Conversation status');?></label>
                                <?php echo erLhcoreClassRenderHelper::renderMultiDropdown( array (
                                    'input_name'     => 'status_conv_id[]',
                                    'optional_field' => erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Choose status'),
                                    'selected_id'    => $input->status_conv_id,
                                    'css_class'      => 'form-control',
                                    'display_name'   => 'name',
                                    'list_function_params' => array(),
                                    'list_function'  => function () {
                                        $items = array();

                                        $item = new StdClass();
                                        $item->name = erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','New mails');
                                        $item->id = erLhcoreClassModelMailconvConversation::STATUS_PENDING;
                                        $items[] = $item;

                                        $item = new StdClass();
                                        $item->name = erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Active mails');
                                        $item->id = erLhcoreClassModelMailconvConversation::STATUS_ACTIVE;
                                        $items[] = $item;

                                        $item = new StdClass();
                                        $item->name = erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Closed mails');
                                        $item->id = erLhcoreClassModelMailconvConversation::STATUS_CLOSED;
                                        $items[] = $item;

                                        return $items;
                                    }
                                )); ?>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="form-group">
                                <label><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('bracket/lists/filter','Message status');?></label>
                                <?php echo erLhcoreClassRenderHelper::renderMultiDropdown( array (
                                    'input_name'     => 'status_msg_id[]',
                                    'optional_field' => erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Choose status'),
                                    'selected_id'    => $input->status_msg_id,
                                    'css_class'      => 'form-control',
                                    'display_name'   => 'name',
                                    'list_function_params' => array(),
                                    'list_function'  => function () {
                                        $items = array();

                                        $item = new StdClass();
                                        $item->name = erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','New');
                                        $item->id = erLhcoreClassModelMailconvMessage::STATUS_PENDING;
                                        $items[] = $item;

                                        $item = new StdClass();
                                        $item->name = erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Active');
                                        $item->id = erLhcoreClassModelMailconvMessage::STATUS_ACTIVE;
                                        $items[] = $item;

                                        $item = new StdClass();
                                        $item->name = erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Responded');
                                        $item->id = erLhcoreClassModelMailconvMessage::STATUS_RESPONDED;
                                        $items[] = $item;

                                        return $items;
                                    }
                                )); ?>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="form-group">
                                <label><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('bracket/lists/filter','Message response status');?></label>
                                <select name="response_type" class="form-control form-control-sm">
                                    <option value="">Select</option>
                                    <option value="0" <?php ($input->response_type === erLhcoreClassModelMailconvMessage::RESPONSE_UNRESPONDED) ? print 'selected="selected"' : null?> >Un-Responded</option>
                                    <option value="1" <?php ($input->response_type === erLhcoreClassModelMailconvMessage::RESPONSE_NOT_REQUIRED) ? print 'selected="selected"' : null?> >Response not required</option>
                                    <option value="2" <?php ($input->response_type === erLhcoreClassModelMailconvMessage::RESPONSE_INTERNAL) ? print 'selected="selected"' : null?> >Our reply message</option>
                                    <option value="3" <?php ($input->response_type === erLhcoreClassModelMailconvMessage::RESPONSE_NORMAL) ? print 'selected="selected"' : null?> >Responded by us</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Sender host E.g gmail.com</label>
                                <input type="text" class="form-control form-control-sm" name="sender_host" value="<?php echo htmlspecialchars($input->sender_host)?>" />
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Sender e-mail</label>
                                <input type="text" class="form-control form-control-sm" name="sender_address" value="<?php echo htmlspecialchars($input->sender_address)?>" />
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>From host E.g gmail.com</label>
                                <input type="text" class="form-control form-control-sm" name="from_host" value="<?php echo htmlspecialchars($input->from_host)?>" />
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('bracket/lists/filter','Message ID');?></label>
                                <input type="text" class="form-control form-control-sm" name="message_id" value="<?php echo htmlspecialchars((string)$input->message_id)?>" />
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="form-group">
                                <label><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('bracket/lists/filter','Phone');?></label>
                                <input type="text" class="form-control form-control-sm" name="phone" value="<?php echo htmlspecialchars($input->phone)?>" />
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Language');?></label>
                                <?php echo erLhcoreClassRenderHelper::renderMultiDropdown( array (
                                    'input_name'     => 'lang_ids[]',
                                    'attr_id'        => 'short_code',
                                    'optional_field' => erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Choose a language'),
                                    'selected_id'    => $input->lang_ids,
                                    'css_class'      => 'form-control',
                                    'display_name'   => function($item) {
                                        return '[' . $item->short_code . '] '.$item->lang_name;
                                    },
                                    'list_function_params' => ['filternot' => ['short_code' => '']],
                                    'list_function'  => 'erLhcoreClassModelSpeechLanguageDialect::getList'
                                )); ?>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="form-group">
                                <label><input type="checkbox" <?php if ($input->no_user == true) : ?>checked="checked"<?php endif;?> name="no_user" value="on" />&nbsp;<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Has not operator assigned')?></label><br/>
                                <label><input type="checkbox" <?php if ($input->hvf == true) : ?>checked="checked"<?php endif;?> name="hvf" value="on" />&nbsp;<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Message has an attachment')?></label><br/>
                                <label><input type="checkbox" name="has_operator" value="1" <?php $input->has_operator == true ? print 'checked="checked"' : ''?> >&nbsp;<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Has operator assigned')?></label>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="col-form-label"><input type="checkbox" name="undelivered" <?php $input->undelivered == 1 ? print ' checked="checked" ' : ''?> value="on" /> <?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Undelivered')?></label><br/>
                                <label class="col-form-label"><input type="checkbox" <?php if ($input->is_followup == true) : ?>checked="checked"<?php endif;?> name="is_followup" value="on" />&nbsp;<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Is followup')?></label>
                            </div>
                        </div>
                    </div>
                </div>

                <?php include(erLhcoreClassDesign::designtpl('elasticsearch/parts/mail_custom_filter_attr_multiinclude.tpl.php')); ?>
            </div>

            <hr class="mt-0">

        </div>
    </div>

    <?php if (isset($total_literal)) : ?>
        <div class="float-end">
            <?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Records in total');?> - <?php echo $total_literal;?>
        </div>
    <?php endif; ?>

    <div class="btn-group" role="group" aria-label="...">
        <input type="submit" name="doSearchSubmit" class="btn btn-primary btn-sm" value="<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Search');?>" />

        <?php if (isset($pages) && $pages->items_total > 0) : $appendPrintExportURL = '';?>
            <?php include(erLhcoreClassDesign::designtpl('lhmailconv/lists/search_panel_append_print_multiinclude.tpl.php'));?>

            <?php if (erLhcoreClassUser::instance()->hasAccessTo('lhmailconv','export_mails')) : ?>
                <button type="button" onclick="return lhc.revealModal({'title' : 'Export', 'height':350, backdrop:true, 'url':'<?php echo $pages->serverURL?>/(export)/1?<?php echo $appendPrintExportURL?>'})" class="btn btn-outline-secondary btn-sm"><span class="material-icons">file_download</span><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Export')?> (<?php echo $pages->items_total?>)</button>
            <?php endif; ?>

        <?php endif; ?>

        <?php if (isset($pages) && erLhcoreClassUser::instance()->hasAccessTo('lhviews','use')) : ?>
            <?php if ($input->view > 0) : ?>
                <input type="hidden" name="view" value="<?php echo $input->view?>" />
            <?php endif; ?>
            <button type="button" onclick="return lhc.revealModal({'title' : 'Export', 'height':350, backdrop:true, 'url':'<?php echo $pages->serverURL?>/(export)/2'})" class="btn btn-outline-secondary btn-sm">
                <span class="material-icons">saved_search</span>
                <?php if ($input->view > 0) : ?>
                    <?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Update view')?>
                <?php else : ?>
                    <?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Save as view')?>
                <?php endif; ?>
            </button>
        <?php endif; ?>
        
        <a class="btn btn-outline-secondary btn-sm" href="<?php echo erLhcoreClassDesign::baseurl('elasticsearch/listmail')?>"><span class="material-icons">refresh</span><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Reset');?></a>

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