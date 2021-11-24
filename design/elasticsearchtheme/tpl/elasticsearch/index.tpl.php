<h1><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Elastic search')?></h1>

<ul>
    <li><a href="<?php echo erLhcoreClassDesign::baseurl('elasticsearch/list')?>"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','List of chats')?></a></li>
    <li><a href="<?php echo erLhcoreClassDesign::baseurl('elasticsearch/listmail')?>"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','List of mails')?></a></li>
    <li><a href="<?php echo erLhcoreClassDesign::baseurl('elasticsearch/interactions')?>"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','List of interactions')?></a></li>
    <li><a href="<?php echo erLhcoreClassDesign::baseurl('elasticsearch/listos')?>"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','List of online sessions')?></a></li>
    <li><a href="<?php echo erLhcoreClassDesign::baseurl('elasticsearch/listop')?>"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','List of online operators history')?></a></li>
    <li><a href="<?php echo erLhcoreClassDesign::baseurl('elasticsearch/listpc')?>"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','List of pending/active chats history')?></a></li>
    <li><a href="<?php echo erLhcoreClassDesign::baseurl('elasticsearch/elastic')?>"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Elastic Search console')?></a></li>
    <li><a href="<?php echo erLhcoreClassDesign::baseurl('elasticsearch/options')?>"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Elastic Search Options')?></a></li>
    <li><a href="<?php echo erLhcoreClassDesign::baseurl('elasticsearch/indices')?>"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Indices')?></a></li>
</ul>

<hr>

<div class="row">
	<div class="col-md-12">
		<div id="status-elastic"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Comparing current elastic structure, please wait...')?></div>
	</div>
</div>

<script>
function updateElasticStructure() {
	$('#elastic-status-checked').hide();
	$('#elastic-status-updating').show();		
	$.postJSON('<?php echo erLhcoreClassDesign::baseurl('elasticsearch/updateelastic')?>/(action)/updateelastic',function(data){
        $('#status-elastic').html(data.result);            
    }); 
};

function crateElasticIndexs() {
	$('#elastic-status-checked').hide();
	$('#elastic-status-updating').show();		
	$.postJSON('<?php echo erLhcoreClassDesign::baseurl('elasticsearch/updateelastic')?>/(action)/createelasticindex',function(data){
        $('#status-elastic').html(data.result);            
    }); 
};

(function() {
	  
  $.postJSON('<?php echo erLhcoreClassDesign::baseurl('elasticsearch/updateelastic')?>/(action)/statuselastic', function(data){
      $('#status-elastic').html(data.result);            
  });
    
})();
</script>