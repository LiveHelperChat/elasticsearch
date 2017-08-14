<form action="" method="get">

<div class="row form-group">
    	
	<div class="col-md-3">
	  <div class="form-group">
		<label><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Date range from');?></label>
			<div class="row">
				<div class="col-md-12">
					<input type="text" class="form-control" name="timefrom" id="id_timefrom" placeholder="E.g <?php echo date('Y-m-d',time()-24*3600)?>" value="<?php echo htmlspecialchars($input->timefrom == null ? date('Y-m-d',time()-24*3600) : $input->timefrom )?>" />
				</div>							
			</div>
		</div>
	</div>
	
	<div class="col-md-3">
	  <div class="form-group">
		<label><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Hour and minute from');?></label>
		<div class="row">				
			<div class="col-md-6">
			    <select name="timefrom_hours" class="form-control">
			        <option value="">Select hour</option>
			        <?php for ($i = 0; $i <= 23; $i++) : ?>
			            <option value="<?php echo $i?>" <?php if (isset($input->timefrom_hours) && $input->timefrom_hours === $i) : ?>selected="selected"<?php endif;?>><?php echo str_pad($i,2, '0', STR_PAD_LEFT);?> h.</option>
			        <?php endfor;?>
			    </select>
			</div>
			<div class="col-md-6">
			    <select name="timefrom_minutes" class="form-control">
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
					<input type="text" class="form-control" name="timeto" id="id_timeto" placeholder="E.g <?php echo date('Y-m-d')?>" value="<?php echo htmlspecialchars($input->timeto)?>" />
				</div>							
			</div>
		</div>
	</div>
	
	<div class="col-md-3">
	  <div class="form-group">
		<label><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Hour and minute to');?></label>
	    <div class="row">				
			<div class="col-md-6">
			    <select name="timeto_hours" class="form-control">
			        <option value="">Select hour</option>
			        <?php for ($i = 0; $i <= 23; $i++) : ?>
			            <option value="<?php echo $i?>" <?php if (isset($input->timeto_hours) && $input->timeto_hours === $i) : ?>selected="selected"<?php endif;?>><?php echo str_pad($i,2, '0', STR_PAD_LEFT);?> h.</option>
			        <?php endfor;?>
			    </select>
			</div>
			<div class="col-md-6">
			    <select name="timeto_minutes" class="form-control">
			        <option value="">Select minute</option>
			        <?php for ($i = 0; $i <= 59; $i++) : ?>
			            <option value="<?php echo $i?>" <?php if (isset($input->timeto_minutes) && $input->timeto_minutes === $i) : ?>selected="selected"<?php endif;?>><?php echo str_pad($i,2, '0', STR_PAD_LEFT);?> m.</option>
			        <?php endfor;?>
			    </select>
			</div>
	    </div>
	  </div>
	</div>

	<div class="col-md-4">
	   <div class="form-group">
    	<label><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Department');?></label>
    	<?php echo erLhcoreClassRenderHelper::renderCombobox( array (
                    'input_name'     => 'department_id',
    				'optional_field' => erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Choose department'),
                    'selected_id'    => $input->department_id,	
    	            'css_class'      => 'form-control',			
                    'list_function'  => 'erLhcoreClassModelDepartament::getList'
            )); ?> 
        </div>
    </div>

    <div class="col-md-4">
	   <div class="form-group">
    	<label><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Department group');?></label>
    	<?php echo erLhcoreClassRenderHelper::renderCombobox( array (
                    'input_name'     => 'department_group_id',
    				'optional_field' => erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Choose department group'),
                    'selected_id'    => $input->department_group_id,	
    	            'css_class'      => 'form-control',			
                    'list_function'  => 'erLhcoreClassModelDepartamentGroup::getList'
            )); ?> 
        </div>   
    </div> 
    
    
    <div class="col-md-4">
	   <div class="form-group">
    	<label><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Group by');?></label>
    	<?php echo erLhcoreClassRenderHelper::renderCombobox( array (
                    'input_name'     => 'group_by',
                    'selected_id'    => $input->group_by,	
    	            'css_class'      => 'form-control',			
                    'list_function'  => 'erLhcoreClassElasticSearchStatistic::getGroupBy'
            )); ?> 
        </div>   
    </div> 
    
    
    
    <div class="col-md-12">
        <div class="btn-group" role="group" aria-label="...">
            <input type="submit" name="doSearch" class="btn btn-default" value="<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Search');?>" />
            <a href="<?php echo erLhcoreClassDesign::baseurl('statistic/statistic')?>/(tab)/pendingvsonlineop/(xls)/1<?php echo erLhcoreClassSearchHandler::getURLAppendFromInput($input)?>" class="btn btn-default">Export XLS</a>
        </div>
	</div>

</div>
	
<script>
	$(function() {
		$('#id_timefrom,#id_timeto').fdatepicker({
			format: 'yyyy-mm-dd'
		});
	});
</script>	
						
</form>



<canvas id="pendingvsonline-chart" width="400" height="300" style="cursor:pointer"></canvas>

<script>
function randomScalingFactor()
{
	return Math.floor((Math.random() * 10) + 1);
}

var chartData = {
    labels: [<?php $counter = 0; foreach ($statistic as $key => $data) : ?><?php ($counter > 0 ? print ',' : '');echo '"',date('Y-m-d H:i',$key),'"';?><?php $counter++;endforeach;?>],
    datasets: [{
        type: 'line',
        label: 'Online Operators',
        borderColor: "rgb(54, 162, 235)",
        borderWidth: 2,
        fill: false,
        data: [<?php $counter = 0; foreach ($statistic as $key => $data) : ?><?php ($counter > 0 ? print ',' : '');echo $data['op_count'];?><?php $counter++;endforeach;?>],
        yAxisID: "y-axis-2"
    }, {
        type: 'bar',
        label: 'Pending chats',
        backgroundColor: "rgb(226, 213, 59)",
        data: [<?php $counter = 0; foreach ($statistic as $key => $data) : ?><?php ($counter > 0 ? print ',' : '');echo $data['pending'];?><?php $counter++;endforeach;?>],
        borderColor: 'white',
        borderWidth: 2,
        yAxisID: "y-axis-1"
    }, {
        type: 'bar',
        label: 'Active Chats',
        backgroundColor: "rgb(93, 164, 35)",
        data: [<?php $counter = 0; foreach ($statistic as $key => $data) : ?><?php ($counter > 0 ? print ',' : '');echo $data['active'];?><?php $counter++;endforeach;?>],
        yAxisID: "y-axis-1"
    }]

};

var ctx = document.getElementById("pendingvsonline-chart").getContext("2d");

new Chart(ctx, {
    type: 'bar',
    data: chartData,
    options: {
        responsive: true,
        title: {
            display: true,
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
            }, {
                type: "linear", // only linear but allow scale type registration. This allows extensions to exist solely for log scale for instance
                display: true,
                position: "right",
                id: "y-axis-2",

                // grid line settings
                gridLines: {
                    drawOnChartArea: false, // only want the grid lines for one axis to show up
                },
            }],
        }
    }
});

</script>
