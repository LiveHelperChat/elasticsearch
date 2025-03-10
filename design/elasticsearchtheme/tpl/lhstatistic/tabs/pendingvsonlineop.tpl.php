<form action="" method="get" autocomplete="off" ng-non-bindable>

<div class="row form-group">

    <div class="col-md-3">
        <div class="form-group">
            <label><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','User');?></label>
            <?php echo erLhcoreClassRenderHelper::renderMultiDropdown( array (
                'input_name'     => 'user_ids[]',
                'optional_field' => erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Select user'),
                'selected_id'    => $input->user_ids,
                'css_class'      => 'form-control',
                'display_name'   => 'name_official',
                'list_function'  => 'erLhcoreClassModelUser::getUserList'
            )); ?>
        </div>
    </div>

    <div class="col-md-3">
        <div class="form-group">
            <label><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','User group');?></label>
            <?php echo erLhcoreClassRenderHelper::renderMultiDropdown( array (
                'input_name'     => 'group_ids[]',
                'optional_field' => erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Select group'),
                'selected_id'    => $input->group_ids,
                'css_class'      => 'form-control',
                'display_name'   => 'name',
                'list_function'  => 'erLhcoreClassModelGroup::getList'
            )); ?>
        </div>
    </div>

    <div class="col-md-3">
	  <div class="form-group">
		<label><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Date range from');?></label>
			<div class="row">
				<div class="col-md-12">
					<input type="text" class="form-control form-control-sm" name="timefrom" id="id_timefrom" placeholder="E.g <?php echo date('Y-m-d',time()-24*3600)?>" value="<?php echo htmlspecialchars($input->timefrom == null ? date('Y-m-d',time()-24*3600) : $input->timefrom )?>" />
				</div>							
			</div>
		</div>
	</div>
	
	<div class="col-md-3">
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
	
	<div class="col-md-3">
	  <div class="form-group">
		<label><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Date range to');?></label>
			<div class="row">
				<div class="col-md-12">
					<input type="text" class="form-control form-control-sm" name="timeto" id="id_timeto" placeholder="E.g <?php echo date('Y-m-d')?>" value="<?php echo htmlspecialchars($input->timeto)?>" />
				</div>							
			</div>
		</div>
	</div>
	
	<div class="col-md-3">
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
               'list_function_params' => array_merge(['sort' => '`name` ASC','limit' => 50],erLhcoreClassUserDep::conditionalDepartmentFilter()),
               'list_function'  => 'erLhcoreClassModelDepartament::getList'
           )); ?>
        </div>
    </div>

    <div class="col-md-3">
	   <div class="form-group">
    	   <label><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Department group');?></label>
           <?php echo erLhcoreClassRenderHelper::renderMultiDropdown( array (
               'input_name'     => 'department_group_ids[]',
               'optional_field' => erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Choose department group'),
               'selected_id'    => $input->department_group_ids,
               'css_class'      => 'form-control',
               'display_name'   => 'name',
               'list_function_params' => array_merge(['sort' => '`name` ASC'],erLhcoreClassUserDep::conditionalDepartmentGroupFilter()),
               'list_function'  => 'erLhcoreClassModelDepartamentGroup::getList'
           )); ?>
        </div>   
    </div> 
    
    
    <div class="col-md-2">
	   <div class="form-group">
    	<label><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Group by');?></label>
    	<?php echo erLhcoreClassRenderHelper::renderCombobox(array(
                    'input_name'     => 'group_by',
                    'selected_id'    => (empty($input->group_by) ? 300000 : $input->group_by),
    	            'css_class'      => 'form-control form-control-sm',
                    'list_function'  => 'erLhcoreClassElasticSearchStatistic::getGroupBy'
            )); ?>
        </div>
    </div>

    <div class="col-md-2">
        <label><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Choose what to display')?></label>
        <div class="btn-block-department">
            <ul class="nav">
                <li class="dropdown">

                    <button type="button" class="btn btn-secondary btn-block btn-sm dropdown-toggle btn-department-dropdown" data-bs-toggle="dropdown" aria-expanded="false">Display <span class="caret"></span></button>

                    <ul class="dropdown-menu" role="menu">
                        <li><label><input type="checkbox" <?php if (isset($_GET['displayChart']) && in_array('PendingChats',$_GET['displayChart'])) : ?>checked="checked"<?php endif?> name="displayChart[]" value="PendingChats"> Pending chats</label></li>
                        <li><label><input type="checkbox" <?php if (isset($_GET['displayChart']) && in_array('ActiveChats',$_GET['displayChart'])) : ?>checked="checked"<?php endif?> name="displayChart[]" value="ActiveChats"> Active chats (Active + In-active)</label></li>
                        <li><label><input type="checkbox" <?php if (isset($_GET['displayChart']) && in_array('OnlineOperators',$_GET['displayChart'])) : ?>checked="checked"<?php endif?> name="displayChart[]" value="OnlineOperators"> Online operators</label></li>

                        <li class="border-top"><label><input type="checkbox" <?php if (isset($_GET['displayChart']) && in_array('StrictlyActive',$_GET['displayChart'])) : ?>checked="checked"<?php endif?> name="displayChart[]" value="StrictlyActive"> Active chats based on operators (Active + In-active)</label></li>
                        <li><label><input type="checkbox" <?php if (isset($_GET['displayChart']) && in_array('PendingChatsOperator',$_GET['displayChart'])) : ?>checked="checked"<?php endif?> name="displayChart[]" value="PendingChatsOperator"> Pending chats assigned to operators</label></li>
                        <li><label><input type="checkbox" <?php if (isset($_GET['displayChart']) && in_array('LiveChats',$_GET['displayChart'])) : ?>checked="checked"<?php endif?> name="displayChart[]" value="LiveChats"> Live Chats (active chats + pending chats - inactive chats)</label></li>
                        <li><label><input type="checkbox" <?php if (isset($_GET['displayChart']) && in_array('InactiveChats',$_GET['displayChart'])) : ?>checked="checked"<?php endif?> name="displayChart[]" value="InactiveChats"> Inactive chats</label></li>

                        <li class="border-top"><label><input type="checkbox" <?php if (isset($_GET['displayChart']) && in_array('FreeSlots',$_GET['displayChart'])) : ?>checked="checked"<?php endif?> name="displayChart[]" value="FreeSlots"> Free slots</label></li>
                        <li><label><input type="checkbox" <?php if (isset($_GET['displayChart']) && in_array('TotalSlots',$_GET['displayChart'])) : ?>checked="checked"<?php endif?> name="displayChart[]" value="TotalSlots"> Total slots</label></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>

    <div class="col-md-12">
        <div class="btn-group" role="group" aria-label="...">
            <input type="submit" name="doSearch" class="btn btn-secondary" value="<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Search');?>" />
            <a href="<?php echo erLhcoreClassDesign::baseurl('statistic/statistic')?>/(tab)/pendingvsonlineop/(xls)/1<?php echo erLhcoreClassSearchHandler::getURLAppendFromInput($input)?>" class="btn btn-secondary">Export XLS</a>
        </div>
    </div>

</div>



<script>
	$(function() {
		$('#id_timefrom,#id_timeto').fdatepicker({
			format: 'yyyy-mm-dd'
		});
        $('.btn-block-department').makeDropdown();
	});
</script>	
						
</form>

<?php if (isset($do_search_first)) : ?>
    <br/>
    <div class="alert alert-info">
        <?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/statistic','Please choose statistic parameters first!');?>
    </div>
<?php else : ?>

<canvas id="pendingvsonline-chart" width="400" height="300" style="cursor:pointer"></canvas>

<script>
var dataSets = [];

<?php $showOperators = false; if (!isset($_GET['displayChart']) || empty($_GET['displayChart']) || is_array($_GET['displayChart']) && in_array('OnlineOperators',$_GET['displayChart'])) : $showOperators = true;?>
dataSets.push({
    type: 'line',
    label: 'Online Operators',
    borderColor: "rgb(54, 162, 235)",
    borderWidth: 2,
    fill: false,
    data: [<?php $counter = 0; foreach ($statistic as $key => $data) : ?><?php ($counter > 0 ? print ',' : '');echo $data['op_count'];?><?php $counter++;endforeach;?>],
    yAxisID: "y-axis-2"
});
<?php endif ?>

<?php if (isset($_GET['displayChart']) && is_array($_GET['displayChart']) && in_array('FreeSlots',$_GET['displayChart'])) : $showOperators = true;?>
dataSets.push({
    type: 'line',
    label: 'Free slots',
    borderColor: "rgb(235,54,69)",
    borderWidth: 2,
    fill: false,
    data: [<?php $counter = 0; foreach ($statistic as $key => $data) : ?><?php ($counter > 0 ? print ',' : '');echo $data['free_slots'];?><?php $counter++;endforeach;?>],
    yAxisID: "y-axis-2"
});
<?php endif ?>

<?php if (isset($_GET['displayChart']) && is_array($_GET['displayChart']) && in_array('TotalSlots',$_GET['displayChart'])) : $showOperators = true;?>
dataSets.push({
    type: 'line',
    label: 'Total slots',
    borderColor: "rgb(23,232,39)",
    borderWidth: 2,
    fill: false,
    data: [<?php $counter = 0; foreach ($statistic as $key => $data) : ?><?php ($counter > 0 ? print ',' : '');echo $data['max_chats'];?><?php $counter++;endforeach;?>],
    yAxisID: "y-axis-2"
});
<?php endif ?>

<?php if (!isset($_GET['displayChart']) || empty($_GET['displayChart']) || is_array($_GET['displayChart']) && in_array('PendingChats',$_GET['displayChart'])) : ?>
dataSets.push({
    type: 'bar',
    label: 'Pending chats',
    backgroundColor: "rgb(189,99,231)",
    data: [<?php $counter = 0; foreach ($statistic as $key => $data) : ?><?php ($counter > 0 ? print ',' : '');echo $data['pending'];?><?php $counter++;endforeach;?>],
    yAxisID: "y-axis-1"
});
<?php endif ?>

<?php if (isset($_GET['displayChart']) && is_array($_GET['displayChart']) && in_array('PendingChatsOperator',$_GET['displayChart'])) : ?>
dataSets.push({
    type: 'bar',
    label: 'Pending chats assigned to operators',
    backgroundColor: "rgb(235,87,154)",
    data: [<?php $counter = 0; foreach ($statistic as $key => $data) : ?><?php ($counter > 0 ? print ',' : '');echo $data['pending_chats'];?><?php $counter++;endforeach;?>],
    yAxisID: "y-axis-1"
});
<?php endif ?>

<?php if (!isset($_GET['displayChart']) || empty($_GET['displayChart']) || is_array($_GET['displayChart']) && in_array('ActiveChats',$_GET['displayChart'])) : ?>
dataSets.push({
    type: 'bar',
    label: 'Active chats (Active + In-active)',
    backgroundColor: "rgb(93, 164, 35)",
    data: [<?php $counter = 0; foreach ($statistic as $key => $data) : ?><?php ($counter > 0 ? print ',' : '');echo $data['active'];?><?php $counter++;endforeach;?>],
    yAxisID: "y-axis-1"
});
<?php endif; ?>

<?php if (isset($_GET['displayChart']) && is_array($_GET['displayChart']) && in_array('StrictlyActive',$_GET['displayChart'])) : ?>
dataSets.push({
    type: 'bar',
    label: 'Active chats based on operators (Active + In-Active)',
    backgroundColor: "rgb(4,118,31)",
    data: [<?php $counter = 0; foreach ($statistic as $key => $data) : ?><?php ($counter > 0 ? print ',' : '');echo $data['active_chats'];?><?php $counter++;endforeach;?>],
    yAxisID: "y-axis-1"
});
<?php endif; ?>


<?php if (isset($_GET['displayChart']) && is_array($_GET['displayChart']) && in_array('LiveChats',$_GET['displayChart'])) : ?>
dataSets.push({
    type: 'bar',
    label: 'Live Chats (Active chats + Pending chats - In-Active chats)',
    backgroundColor: "rgb(164,123,35)",
    data: [<?php $counter = 0; foreach ($statistic as $key => $data) : ?><?php ($counter > 0 ? print ',' : '');echo $data['live_chats'];?><?php $counter++;endforeach;?>],
    yAxisID: "y-axis-1"
});
<?php endif; ?>

<?php if (isset($_GET['displayChart']) && is_array($_GET['displayChart']) && in_array('InactiveChats',$_GET['displayChart'])) : ?>
dataSets.push({
    type: 'bar',
    label: 'In-Active chats',
    backgroundColor: "rgb(103,103,103)",
    data: [<?php $counter = 0; foreach ($statistic as $key => $data) : ?><?php ($counter > 0 ? print ',' : '');echo $data['inactive_chats'];?><?php $counter++;endforeach;?>],
    yAxisID: "y-axis-1"
});
<?php endif; ?>

var chartData = {
    labels: [<?php $counter = 0; foreach ($statistic as $key => $data) : ?><?php ($counter > 0 ? print ',' : '');echo '"',date('Y-m-d H:i',$key),'"';?><?php $counter++;endforeach;?>],
    datasets: dataSets
};

var ctx = document.getElementById("pendingvsonline-chart").getContext("2d");

new Chart(ctx, {
    type: 'bar',
    data: chartData,
    options: {
        onClick: function(evt, elements) {
            if (elements && elements.length > 0) {
                // Get the clicked element
                const element = elements[0];

                // Get the dataset index and data index
                const datasetIndex = element._datasetIndex;
                const dataIndex = element._index;

                // Get the value of the clicked bar
                const value = this.data.datasets[datasetIndex].data[dataIndex];

                // Get the label (might be useful for context)
                const label = this.data.labels[dataIndex];



                lhc.revealModal({'title' : <?php echo json_encode(erTranslationClassLhTranslation::getInstance()->getTranslation('chat/chatpreview','Momentary statistic at'))?> + ' - ' + label, 'iframe' : true,'height' : '600px','url':'<?php echo erLhcoreClassDesign::baseurl('elasticsearch/statisticpending')?><?php echo erLhcoreClassSearchHandler::getURLAppendFromInput($input)?>?date='+encodeURI(label)});


                // You can call your custom function here with the value
                /*handleBarClick(label, value, datasetIndex);*/
            }
        },
        responsive: true,
        title: {
            display: false,
            text: 'Pending/Active Chats VS Online Operators'
        },
        tooltips: {
            mode: 'index',
            intersect: true
        },
        scales: {
            yAxes: [{
                type: "linear", // only linear but allow scale type registration. This allows extensions to exist solely for log scale for instance
                display: true,
                position: "left",
                id: "y-axis-1",
                ticks: {
                    beginAtZero: true // Start the scale at 0
                }
            }<?php if ($showOperators == true) : ?>,
            {
                type: "linear", // only linear but allow scale type registration. This allows extensions to exist solely for log scale for instance
                display: true,
                position: "right",
                id: "y-axis-2",
                ticks: {
                    beginAtZero: true // Start the scale at 0
                },
                gridLines: {
                    drawOnChartArea: false, // only want the grid lines for one axis to show up
                },
            }<?php endif?>],
        }
    }
});

</script>
<?php endif; ?>