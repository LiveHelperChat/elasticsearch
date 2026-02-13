<?php

$tpl = erLhcoreClassTemplate::getInstance('elasticsearch/elastic.tpl.php');

$command = isset($_POST['Query']) ? $_POST['Query'] : '';
$index = isset($_POST['Index']) ? $_POST['Index'] : 'chat';
$response = '';

// Client
$esSearchHandler = erLhcoreClassElasticClient::getHandler();

// Fetch Elasticsearch cluster information
$clusterInfo = array();
try {
    // Get basic cluster info (includes version)
    $info = $esSearchHandler->info();
    $clusterInfo['version'] = isset($info['version']['number']) ? $info['version']['number'] : 'N/A';
    $clusterInfo['cluster_name'] = isset($info['cluster_name']) ? $info['cluster_name'] : 'N/A';
    $clusterInfo['lucene_version'] = isset($info['version']['lucene_version']) ? $info['version']['lucene_version'] : 'N/A';
    
    // Get cluster health
    $health = $esSearchHandler->cluster()->health();
    $clusterInfo['status'] = isset($health['status']) ? $health['status'] : 'N/A';
    $clusterInfo['number_of_nodes'] = isset($health['number_of_nodes']) ? $health['number_of_nodes'] : 0;
    $clusterInfo['number_of_data_nodes'] = isset($health['number_of_data_nodes']) ? $health['number_of_data_nodes'] : 0;
    $clusterInfo['active_shards'] = isset($health['active_shards']) ? $health['active_shards'] : 0;
    $clusterInfo['relocating_shards'] = isset($health['relocating_shards']) ? $health['relocating_shards'] : 0;
    $clusterInfo['unassigned_shards'] = isset($health['unassigned_shards']) ? $health['unassigned_shards'] : 0;
    
    // Get indices stats
    $indicesStats = $esSearchHandler->cat()->indices(array('format' => 'json'));
    $clusterInfo['indices_count'] = is_array($indicesStats) ? count($indicesStats) : 0;
} catch (Exception $e) {
    $clusterInfo['error'] = $e->getMessage();
}

if (isset($_POST['doSearch']))
{
    $sessionElasticStatistic = erLhcoreClassModelESChat::getSession();
    
    $sparams = array();
    
    $sparams['body'] = json_decode($_POST['Query'], true);
    
    // Index
    $sparams['index'] = $index;
    
    try {
        // Statistic
        $response = $esSearchHandler->search($sparams);
    } catch (Exception $e) {
        $response = $e;
    }
}

$tpl->set('command',$command);
$tpl->set('index',$index);
$tpl->set('response',$response);
$tpl->set('clusterInfo', $clusterInfo);

$Result['content'] = $tpl->fetch();

$Result['path'] = array(
	array('url' => erLhcoreClassDesign::baseurl('elasticsearch/index'), 'title' => erTranslationClassLhTranslation::getInstance()->getTranslation('system/configuration','Elastic Search')),
	array('title' => erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/index','Console'))
);

?>