<?php

if (!$currentUser->validateCSFRToken($Params['user_parameters_unordered']['csfr'])) {
    die('Invalid CSFR Token');
    exit;
}

$item = \LiveHelperChatExtension\elasticsearch\providers\Index\VectorStorage::fetch($Params['user_parameters']['id'],$Params['user_parameters']['index']);

if (is_object($item)) {
    $rootId = $item->id;
    $rootIndex = $item->meta_data['index'];

    // Delete all children first
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
        // Children might not exist, continue
    }

    // Delete the root document
    $item->removeThis();
}

erLhcoreClassModule::redirect('elasticsearch/listvector');
exit;
