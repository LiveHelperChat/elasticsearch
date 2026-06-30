<?php

$tpl = erLhcoreClassTemplate::getInstance('elasticsearch/newvector.tpl.php');

\LiveHelperChatExtension\elasticsearch\providers\Index\VectorStorage::getSession();

$item = new \LiveHelperChatExtension\elasticsearch\providers\Index\VectorStorage();

$errors = array();
$savedItems = array();

if (isset($_POST['Update'])) {
    if (!isset($_POST['csfr_token']) || !$currentUser->validateCSFRToken($_POST['csfr_token'])) {
        erLhcoreClassModule::redirect('elasticsearch/listvector');
        exit;
    }

    // Validation
    $item->name = trim($_POST['Name']);
    $item->dep_id = (int)$_POST['DepId'];
    $content = trim($_POST['Content']);

    if ($item->name == '') {
        $errors[] = erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin', 'Name is required');
    }

    if ($content == '') {
        $errors[] = erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin', 'Content is required');
    }

    if (empty($errors)) {
        try {
            $embeder = \LiveHelperChatExtension\elasticsearch\providers\Helpers\Embeder::getInstance();
            $response = $embeder->embedDocuments(array($content));

            $chunks = $response['chunk_texts'];
            $embeddings = $response['embeddings'];
            $now = time() * 1000;

            \LiveHelperChatExtension\elasticsearch\providers\Index\VectorStorage::getSession();

            $chunkCount = count($chunks);

            $indexSave = \LiveHelperChatExtension\elasticsearch\providers\Index\VectorStorage::$indexName . '-' . \LiveHelperChatExtension\elasticsearch\providers\Index\VectorStorage::$elasticType;

            // Save root chunk first to get its ID
            $rootDoc = new \LiveHelperChatExtension\elasticsearch\providers\Index\VectorStorage();
            $rootDoc->meta_data['index'] = $indexSave;
            $rootDoc->name = $item->name;
            $rootDoc->dep_id = $item->dep_id;
            $rootDoc->content = $chunks[0];
            $rootDoc->vector_storage = $embeddings[0];
            $rootDoc->created_at = $now;
            $rootDoc->parent_id = '0';
            $rootDoc->original_content = $content;
            $rootDoc->saveThis();

            // Save remaining chunks as children
            if ($chunkCount > 1) {
               
                $savedItems = array();
                for ($idx = 1; $idx < $chunkCount; $idx++) {
                    $docName = $item->name . ' (' . erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin', 'chunk') . ' ' . ($idx + 1) . '/' . $chunkCount . ')';
                    $vectorDoc = new \LiveHelperChatExtension\elasticsearch\providers\Index\VectorStorage();
                    $vectorDoc->name = $docName;
                    $vectorDoc->dep_id = $item->dep_id;
                    $vectorDoc->content = $chunks[$idx];
                    $vectorDoc->vector_storage = $embeddings[$idx];
                    $vectorDoc->created_at = $now;
                    $vectorDoc->parent_id = $rootDoc->id;
                    $vectorDoc->original_content = '';

                    $savedItems[$indexSave][] = $vectorDoc;
                }

                \LiveHelperChatExtension\elasticsearch\providers\Index\VectorStorage::bulkSave($savedItems, array('custom_index' => true));
            }

            // Redirect to list after successful save
            erLhcoreClassModule::redirect('elasticsearch/listvector');
            exit;
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }
    }

    $item->content = $content;
}

if (isset($_POST['Cancel'])) {
    erLhcoreClassModule::redirect('elasticsearch/listvector');
    exit;
}

$tpl->set('item', $item);
$tpl->set('errors', $errors);

$Result['content'] = $tpl->fetch();

$Result['path'] = array(
    array(
        'url' => erLhcoreClassDesign::baseurl('elasticsearch/index'),
        'title' => erTranslationClassLhTranslation::getInstance()->getTranslation('lhelasticsearch/module', 'Elastic Search')
    ),
    array(
        'url' => erLhcoreClassDesign::baseurl('elasticsearch/listvector'),
        'title' => erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin', 'Vector Storage Documents')
    ),
    array(
        'title' => erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin', 'New Document')
    )
);
