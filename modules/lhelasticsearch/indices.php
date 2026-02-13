<?php
$tpl = erLhcoreClassTemplate::getInstance('elasticsearch/indices.tpl.php');

// Handle AJAX request for stats
if (isset($_GET['load_stats']) && $_GET['load_stats'] == '1' && isset($_GET['indice'])) {
    $indice = $_GET['indice'];
    
    try {
        $stats = erLhcoreClassElasticClient::getHandler()->indices()->stats(array('index' => $indice));
        
        $docs = $stats['_all']['total']['docs']['count'];
        $deleted = $stats['_all']['total']['docs']['deleted'];
        $sizeBytes = $stats['_all']['total']['store']['size_in_bytes'];
        $sizeMB = round($sizeBytes / 1048576, 2);
        
        $statsData = array(
            'success' => true,
            'docs' => $docs,
            'deleted' => $deleted,
            'size' => $sizeMB
        );
    } catch (Exception $e) {
        $statsData = array(
            'success' => false,
            'error' => $e->getMessage()
        );
    }
    
    header('Content-Type: application/json');
    echo json_encode($statsData);
    exit;
}

if (ezcInputForm::hasPostData()) {

    if (!isset($_POST['csfr_token']) || !$currentUser->validateCSFRToken($_POST['csfr_token'])) {
        erLhcoreClassModule::redirect('elasticsearch/indices');
        exit;
    }

    foreach ($_POST['indices'] as $indice) {
        erLhcoreClassElasticClient::getHandler()->indices()->delete(array(
            'index' => $indice
        ));
    }
}

// Get indices with optional search filter
if (isset($_GET['search']) && $_GET['search'] != '') {
    $searchPattern = $_GET['search'];
    
    // If user provides search without wildcard, add it
    if (strpos($searchPattern, '*') === false) {
        $searchPattern = '*' . $searchPattern . '*';
    }
    
    $tpl->set('indices',erLhcoreClassElasticClient::getHandler()->indices()->getAliases(array('index' => $searchPattern)));
} else {
    // No search provided, return empty array
    $tpl->set('indices', array());
}

$Result['content'] = $tpl->fetch();
$Result['path'] = array(
    array(
        'url' => erLhcoreClassDesign::baseurl('elasticsearch/index'),
        'title' => erTranslationClassLhTranslation::getInstance()->getTranslation('lhelasticsearch/module', 'Elastic Search')
    ),
    array(
        'url' => erLhcoreClassDesign::baseurl('elasticsearch/list'),
        'title' => erTranslationClassLhTranslation::getInstance()->getTranslation('lhelasticsearch/list', 'Indices')
    )
);
