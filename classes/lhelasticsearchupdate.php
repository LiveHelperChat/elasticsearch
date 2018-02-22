<?php

class erLhcoreClassElasticSearchUpdate
{
    public static function getElasticStatus($definition, $indexSingle = null)
    {
        $typeStatus = array();

        if ($indexSingle === null) {
            $settings = erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionElasticsearch')->settings;

            $mainIndex = $settings['index'];
            $additional_indexes = $settings['additional_indexes'];

            $elasticIndexs = array_merge(array(
                $mainIndex
            ), $additional_indexes);
        } else {
            $elasticIndexs[] = $indexSingle;
        }

        foreach ($elasticIndexs as $elasticIndex) {
            
            $elasticData = erLhcoreClassElasticClient::getHandler()->indices()->getMapping(array(
                'index' => $elasticIndex
            ));

            $currentMappingData = $elasticData[$elasticIndex]['mappings'];

            if (isset($definition[$elasticIndex])) {
                foreach ($definition[$elasticIndex]['types'] as $type => $typeDefinition) {

                    if (isset($currentMappingData[$type])) {

                        $status = array();
                        
                        $currentTypeProperties = $currentMappingData[$type]['properties'];
                        
                        // Add property
                        foreach ($typeDefinition as $property => $propertyData) {
                            
                            if (! isset($currentTypeProperties[$property])) {
                                
                                $status[] = '[' . $property . '] property not found';
                                
                                $params = array(
                                    'index' => $elasticIndex,
                                    'type' => $type,
                                    'body' => array(
                                        $type => array(
                                            'properties' => array(
                                                $property => $propertyData
                                            )
                                        )
                                    )
                                );
                                
                                $typeStatus[$type]['actions']['type_property_add'][] = $params;
                            }
                        }
                                                                        
                        if (! empty($status)) {
                            $typeStatus[$type]['error'] = true;
                            $typeStatus[$type]['status'] = implode(', ', $status);
                        }
                    } else {

                        // Add types
                        $typeStatus[$type]['error'] = true;
                        $typeStatus[$type]['status'] = 'type add in index ' . $elasticIndex;
                        
                        $params = array(
                            'index' => $elasticIndex,
                            'type' => $type,
                            'body' => array(
                                $type => array(
                                    'properties' => $typeDefinition
                                )
                            )
                        );
                        
                        $typeStatus[$type]['actions']['type_add'][] = $params;
                    }
                }
            }
            
            // Remove types
            foreach (array_keys($currentMappingData) as $type) {
                
                if (! isset($definition[$elasticIndex]['types'][$type])) {
                    
                    $typeStatus[$type]['error'] = true;
                    $typeStatus[$type]['status'] = 'type removed in index ' . $elasticIndex;
                    
                    $params = array(
                        'index' => $elasticIndex,
                        'type' => $type
                    );
                    
                    $typeStatus[$type]['actions']['type_delete'][] = $params;
                }
            }
        }
        
        return $typeStatus;
    }

    public static function doElasticUpdate($definition, $indexSingle = null)
    {
        $errorMessages = array();
        
        $updateInformation = self::getElasticStatus($definition, $indexSingle);

        foreach ($updateInformation as $type => $typeData) {
            
            if ($typeData['error'] == true) {
                
                foreach ($typeData['actions'] as $actionType => $actionParams) {
                    
                    foreach ($actionParams as $params) {
                        
                        try {
                            
                            if ($actionType == 'type_add') {
                                erLhcoreClassElasticClient::getHandler()->indices()->putMapping($params);
                            } elseif ($actionType == 'type_delete') {
                                erLhcoreClassElasticClient::getHandler()->indices()->deleteMapping($params);
                            } elseif ($actionType == 'type_property_add') {
                                erLhcoreClassElasticClient::getHandler()->indices()->putMapping($params);
                            } elseif ($actionType == 'type_property_delete') {
                                // Not used now
                            }
                        } catch (Exception $e) {
                            $errorMessages[] = $e->getMessage();
                        }
                    }
                }
            }
        }
        
        return $errorMessages;
    }

    public static function doCreateElasticIndex()
    {        
        $settings = erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionElasticsearch')->settings;
        
        $elasticIndex =$settings['index'];
        
        $indisis = array();
        
        if (erLhcoreClassElasticClient::getHandler()->indices()->exists(array(
            'index' => $elasticIndex
        )) == false) {
            $indisis[] = erLhcoreClassElasticClient::getHandler()->indices()->create(array(
                'index' => $elasticIndex
            ));
        }
        
        foreach ($settings['additional_indexes'] as $index) {
            if (erLhcoreClassElasticClient::getHandler()->indices()->exists(array(
                'index' => $index
            )) == false) {
                $indisis[] = erLhcoreClassElasticClient::getHandler()->indices()->create(array(
                    'index' => $index
                ));
            }
        }
        
        foreach ($indisis as $item) {
            if ($item == false) {
                return false;
            }
        }
        
        return true;
    }

    public static function doDeleteElasticIndex()
    {                
        $settings = erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionElasticsearch')->settings;
        
        $elasticIndex = $settings['index'];
        
        $indisis = array();
        
        if (erLhcoreClassElasticClient::getHandler()->indices()->exists(array(
            'index' => $elasticIndex
        )) == false) {
            $indisis[] = erLhcoreClassElasticClient::getHandler()->indices()->delete(array(
                'index' => $elasticIndex
            ));
        }
        
        foreach ($settings['additional_indexes'] as $index) {
            if (erLhcoreClassElasticClient::getHandler()->indices()->exists(array(
                'index' => $index
            )) == false) {
                $indisis[] = erLhcoreClassElasticClient::getHandler()->indices()->delete(array(
                    'index' => $index
                ));
            }
        }
        
        foreach ($indisis as $item) {
            if ($item == false) {
                return false;
            }
        }
        
        return true;
    }

    public static function elasticIndexExist()
    {       
        $settings = erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionElasticsearch')->settings;
        
        $elasticIndex = $settings['index'];
        
        $indisis = array();
        $indisis[] = erLhcoreClassElasticClient::getHandler()->indices()->exists(array(
            'index' => $elasticIndex
        ));
        
        foreach ($settings['additional_indexes'] as $index) {
            $indisis[] = erLhcoreClassElasticClient::getHandler()->indices()->exists(array(
                'index' => $index
            ));
        }
        
        foreach ($indisis as $item) {
            if ($item == false) {
                return false;
            }
        }
        
        return true;
    }
}