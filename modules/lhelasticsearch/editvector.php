<?php

$tpl = erLhcoreClassTemplate::getInstance('elasticsearch/editvector.tpl.php');

$item = \LiveHelperChatExtension\elasticsearch\providers\Index\VectorStorage::fetch($Params['user_parameters']['id'],$Params['user_parameters']['index']);

if (!is_object($item)) {
    erLhcoreClassModule::redirect('elasticsearch/listvector');
    exit;
}

$rootId = $item->id;
$rootIndex = $item->meta_data['index'];

// Load children chunks for display
try {
    $children = \LiveHelperChatExtension\elasticsearch\providers\Index\VectorStorage::getList(array(
        'limit' => 10000,
        'offset' => 0,
        'body' => array(
            'query' => array(
                'bool' => array(
                    'filter' => array(
                        array('term' => array('parent_id' => $rootId))
                    )
                )
            )
        )
    ));
} catch (Exception $e) {
    $children = array();
}

// Load original_content for the edit form (fall back to content for legacy docs)
$editContent = ($item->original_content != '') ? $item->original_content : $item->content;

$errors = array();

if (isset($_POST['Update'])) {
    if (!isset($_POST['csfr_token']) || !$currentUser->validateCSFRToken($_POST['csfr_token'])) {
        erLhcoreClassModule::redirect('elasticsearch/listvector');
        exit;
    }

    $name = trim($_POST['Name']);
    $depId = (int)$_POST['DepId'];
    $content = trim($_POST['Content']);

    if ($name == '') {
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

            // Delete all existing children first
            try {
                $children = \LiveHelperChatExtension\elasticsearch\providers\Index\VectorStorage::getList(array(
                    'limit' => 10000,
                    'offset' => 0,
                    'body' => array(
                        'query' => array(
                            'bool' => array(
                                'filter' => array(
                                    array('term' => array('parent_id' => $rootId))
                                )
                            )
                        )
                    )
                ));

                if (!empty($children)) {
                    foreach ($children as $child) {
                        $child->meta_data = array('index' => $rootIndex);
                        $child->removeThis();
                    }
                }
            } catch (Exception $e) {
                // Children might not exist (legacy docs), continue
            }

            $chunkCount = count($chunks);

            // Save root chunk with updated content
            $item->name = $name;
            $item->dep_id = $depId;
            $item->content = $chunks[0];
            $item->vector_storage = $embeddings[0];
            $item->parent_id = '0';
            $item->original_content = $content;
            $item->created_at = $item->created_at > 0 ? $item->created_at : $now;
            $item->updateThis();

            // Save remaining chunks as children
            if ($chunkCount > 1) {
                $indexSave = \LiveHelperChatExtension\elasticsearch\providers\Index\VectorStorage::$indexName . '-' . \LiveHelperChatExtension\elasticsearch\providers\Index\VectorStorage::$elasticType;
                $savedItems = array();

                for ($idx = 1; $idx < $chunkCount; $idx++) {
                    $docName = $name . ' (' . erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin', 'chunk') . ' ' . ($idx + 1) . '/' . $chunkCount . ')';
                    $vectorDoc = new \LiveHelperChatExtension\elasticsearch\providers\Index\VectorStorage();
                    $vectorDoc->name = $docName;
                    $vectorDoc->dep_id = $depId;
                    $vectorDoc->content = $chunks[$idx];
                    $vectorDoc->vector_storage = $embeddings[$idx];
                    $vectorDoc->created_at = $now;
                    $vectorDoc->parent_id = $item->id;
                    $vectorDoc->original_content = '';

                    $savedItems[$indexSave][] = $vectorDoc;
                }

                \LiveHelperChatExtension\elasticsearch\providers\Index\VectorStorage::bulkSave($savedItems, array('custom_index' => true));
            }

            erLhcoreClassModule::redirect('elasticsearch/listvector');
            exit;
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }
    }

    $item->name = $name;
    $item->content = $content;
    $editContent = $content;
}

if (isset($_POST['Cancel'])) {
    erLhcoreClassModule::redirect('elasticsearch/listvector');
    exit;
}

// Set the display content to original_content for the form
$item->content = $editContent;
$tpl->set('item', $item);
$tpl->set('children', $children);
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
        'title' => erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin', 'Edit Document')
    )
);
