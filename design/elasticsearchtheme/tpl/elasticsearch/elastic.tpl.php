<h1><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Elastic search console')?></h1>

<?php if (!empty($clusterInfo)) : ?>
<div class="alert alert-info" style="margin-bottom: 20px;">
    <?php if (isset($clusterInfo['error'])) : ?>
        <div class="text-danger"><strong>Error:</strong> <?php echo htmlspecialchars($clusterInfo['error'])?></div>
    <?php else : ?>
        <div class="row">
            <div class="col-md-6">
                <ul class="list-unstyled">
                    <li><strong>Elasticsearch Version:</strong> <?php echo htmlspecialchars($clusterInfo['version'])?></li>
                    <li><strong>Lucene Version:</strong> <?php echo htmlspecialchars($clusterInfo['lucene_version'])?></li>
                    <li><strong>Cluster Name:</strong> <?php echo htmlspecialchars($clusterInfo['cluster_name'])?></li>
                    <li><strong>Cluster Status:</strong> 
                        <span class="badge badge-<?php echo $clusterInfo['status'] == 'green' ? 'success' : ($clusterInfo['status'] == 'yellow' ? 'warning' : 'danger')?>">
                            <?php echo strtoupper(htmlspecialchars($clusterInfo['status']))?>
                        </span>
                    </li>
                </ul>
            </div>
            <div class="col-md-6">
                <ul class="list-unstyled">
                    <li><strong>Total Nodes:</strong> <?php echo htmlspecialchars($clusterInfo['number_of_nodes'])?></li>
                    <li><strong>Data Nodes:</strong> <?php echo htmlspecialchars($clusterInfo['number_of_data_nodes'])?></li>
                    <li><strong>Indices:</strong> <?php echo htmlspecialchars($clusterInfo['indices_count'])?></li>
                    <li><strong>Active Shards:</strong> <?php echo htmlspecialchars($clusterInfo['active_shards'])?> 
                        <?php if ($clusterInfo['relocating_shards'] > 0) : ?>
                            <small>(<?php echo htmlspecialchars($clusterInfo['relocating_shards'])?> relocating)</small>
                        <?php endif; ?>
                        <?php if ($clusterInfo['unassigned_shards'] > 0) : ?>
                            <small class="text-danger">(<?php echo htmlspecialchars($clusterInfo['unassigned_shards'])?> unassigned)</small>
                        <?php endif; ?>
                    </li>
                </ul>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<form action="" method="post" ng-non-bindable>
    <div class="form-group">
		<label><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Index')?></label> 
		<input type="text" class="form-control" name="Index" value="<?php echo htmlspecialchars($index)?>">
	</div>

    <textarea class="form-control" style="font-size:11px" rows="10" name="Query"><?php echo htmlspecialchars($command)?></textarea>
    <br>
    <input type="submit" name="doSearch" value="<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Submit')?>" class="btn btn-secondary" />
</form>
<br/>
<pre style="font-size:11px;">
<?php echo htmlspecialchars(print_r($response,true))?>
</pre>