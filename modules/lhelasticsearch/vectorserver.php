<?php

$tpl = erLhcoreClassTemplate::getInstance('elasticsearch/vectorserver.tpl.php');

$text = isset($_POST['Text']) ? $_POST['Text'] : '';
$queryResponse = null;
$docsResponse = null;
$searchResponse = null;
$searchPhrase = isset($_POST['SearchPhrase']) ? $_POST['SearchPhrase'] : '';

$embeder = \LiveHelperChatExtension\elasticsearch\providers\Helpers\Embeder::getInstance();

if (isset($_POST['doSearchVector'])) {
    if (!isset($_POST['csfr_token']) || !$currentUser->validateCSFRToken($_POST['csfr_token'])) {
        die();
    }

    $searchPhrase = trim($searchPhrase);

    if ($searchPhrase !== '') {
        try {
            // Step 1: Get embedding for the search phrase
            $embedData = $embeder->embedQuery($searchPhrase);
            $queryVector = $embedData['embed'];

            // Step 2: Search VectorStorage with script_score
            \LiveHelperChatExtension\elasticsearch\providers\Index\VectorStorage::getSession();

            /*$params = array(
                'limit' => 10,
                'offset' => 0,
                'body' => array(
                    //'min_score' => 1.25,
                    'query' => array(
                        'script_score' => array(
                            'query' => array(
                                'bool' => array(
                                    'filter' => array(
                                        'exists' => array('field' => 'vector_storage')
                                    )
                                )
                            ),
                            'script' => array(
                                'source' => "cosineSimilarity(params.query_vector, 'vector_storage') + 1.0",
                                'params' => array(
                                    'query_vector' => $queryVector
                                )
                            )
                        )
                    )
                )
            );*/

            $params = array(
                'limit' => 10,
                'offset' => 0,
                'body' => array(
                    'knn' => array(
                        'field' => 'vector_storage',
                        'query_vector' => $queryVector,
                        'k' => 10,
                        'num_candidates' => 100,
                        'boost' =>          0.8
                    ),
                    'query' => array(
                        'match' => array(
                            'content' => array(
                                'query' => $searchPhrase,
                                'boost' =>  0.2
                            )
                        )
                    ),
                    //'_source' => array('name', 'content', 'dep_id', 'created_at')
                )
            );


            $items = \LiveHelperChatExtension\elasticsearch\providers\Index\VectorStorage::getList($params);

            $searchResults = array();
            foreach ($items as $item) {
                $searchResults[] = array(
                    'id' => $item->id,
                    'name' => $item->name,
                    'content' => $item->content,
                    'dep_id' => $item->dep_id,
                    'created_at' => $item->created_at,
                    'score' => isset($item->meta_data['score']) ? $item->meta_data['score'] : null,
                    'index' => isset($item->meta_data['index']) ? $item->meta_data['index'] : null
                );
            }

            $searchResponse = array(
                'error' => false,
                'query' => $searchPhrase,
                'embed_dimensions' => count($queryVector),
                'total_found' => count($searchResults),
                'results' => $searchResults
            );
        } catch (Exception $e) {
            $searchResponse = array('error' => true, 'message' => $e->getMessage());
        }
    }
}

if (isset($_POST['doEmbedQuery'])) {
    if (!isset($_POST['csfr_token']) || !$currentUser->validateCSFRToken($_POST['csfr_token'])) {
        die();
    }

    try {
        $queryResponse = $embeder->embedQuery($text);
    } catch (Exception $e) {
        $queryResponse = array('error' => true, 'message' => $e->getMessage());
    }
}

if (isset($_POST['doEmbedDocuments'])) {
    if (!isset($_POST['csfr_token']) || !$currentUser->validateCSFRToken($_POST['csfr_token'])) {
        die();
    }

    try {
        $docsResponse = $embeder->embedDocuments(array($text));
    } catch (Exception $e) {
        $docsResponse = array('error' => true, 'message' => $e->getMessage());
    }
}

$tpl->set('text', $text);
$tpl->set('embedServerUrl', $embeder->getServerUrl());
$tpl->set('queryResponse', $queryResponse);
$tpl->set('docsResponse', $docsResponse);
$tpl->set('searchPhrase', $searchPhrase);
$tpl->set('searchResponse', $searchResponse);

$Result['content'] = $tpl->fetch();

$Result['path'] = array(
    array('url' => erLhcoreClassDesign::baseurl('elasticsearch/index'), 'title' => erTranslationClassLhTranslation::getInstance()->getTranslation('system/configuration','Elastic Search')),
    array('title' => erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Vector Embedding Server'))
);