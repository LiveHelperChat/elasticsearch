<?php

$tpl = erLhcoreClassTemplate::getInstance('elasticsearch/listvector.tpl.php');

\LiveHelperChatExtension\elasticsearch\providers\Index\VectorStorage::getSession();

$pages = new lhPaginator();
$pages->serverURL = erLhcoreClassDesign::baseurl('elasticsearch/listvector');
$pages->items_total = \LiveHelperChatExtension\elasticsearch\providers\Index\VectorStorage::getCount(array(
    'body' => array(
        'query' => array(
            'bool' => array(
                'should' => array(
                    array('term' => array('parent_id' => '0')),
                    array('bool' => array('must_not' => array('exists' => array('field' => 'parent_id'))))
                ),
                'minimum_should_match' => 1
            )
        )
    )
));
$pages->setItemsPerPage(30);
$pages->paginate();

$items = array();
$childrenCounts = array();

if ($pages->items_total > 0) {
    $items = \LiveHelperChatExtension\elasticsearch\providers\Index\VectorStorage::getList(array(
        'offset' => $pages->low,
        'limit' => $pages->items_per_page,
        'body' => array(
            'sort' => array(
                'created_at' => array(
                    'order' => 'desc'
                )
            ),
            'query' => array(
                'bool' => array(
                    'should' => array(
                        array('term' => array('parent_id' => '0')),
                        array('bool' => array('must_not' => array('exists' => array('field' => 'parent_id'))))
                    ),
                    'minimum_should_match' => 1
                )
            )
        )
    ));

    // Get children counts for all root documents
    if (!empty($items)) {
        try {
            $childrenAgg = \LiveHelperChatExtension\elasticsearch\providers\Index\VectorStorage::getAggregation(array(
                'body' => array(
                    'query' => array(
                        'bool' => array(
                            'filter' => array(
                                array('bool' => array('must_not' => array('term' => array('parent_id' => '0'))))
                            )
                        )
                    ),
                    'aggs' => array(
                        'children_per_parent' => array(
                            'terms' => array('field' => 'parent_id', 'size' => 10000)
                        )
                    )
                )
            ), array(), 'children_per_parent');

            foreach ($childrenAgg as $aggItem) {
                $childrenCounts[$aggItem->key] = $aggItem->doc_count;
            }
        } catch (Exception $e) {
            // Aggregation might fail on legacy ES versions without parent_id field mapping
        }
    }
}

// Attach children counts
foreach ($items as $item) {
    $item->children_count = 1; // root itself
    if (isset($childrenCounts[$item->id])) {
        $item->children_count += $childrenCounts[$item->id];
    }
}

$tpl->set('items', $items);
$tpl->set('pages', $pages);

$Result['content'] = $tpl->fetch();

$Result['path'] = array(
    array(
        'url' => erLhcoreClassDesign::baseurl('elasticsearch/index'),
        'title' => erTranslationClassLhTranslation::getInstance()->getTranslation('lhelasticsearch/module', 'Elastic Search')
    ),
    array(
        'title' => erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin', 'Vector Storage Documents')
    )
);
