<?php
#[\AllowDynamicProperties]
class erLhcoreClassElasticSearchUpdate
{
    public static function getElasticStatus($definition, $elasticIndex)
    {
        $typeStatus = array();


        $elasticData = erLhcoreClassElasticClient::getHandler()->indices()->getMapping(array(
            'index' => $elasticIndex
        ));

        $currentMappingData = $elasticData[$elasticIndex]['mappings'];

        $status = array();

        if (isset( $currentMappingData['properties'])) {
            $currentTypeProperties = $currentMappingData['properties'];
        } else {
            $currentTypeProperties = array();
        }

        // Add property
        foreach ($definition as $property => $propertyData) {

            if (!isset($currentTypeProperties[$property])) {

                $status[] = '[' . $elasticIndex . '] [' . $property . '] property not found';

                $params = array(
                    'index' => $elasticIndex,
                    'body' => array(
                        'properties' => array(
                            $property => $propertyData
                        )
                    )
                );

                $typeStatus['_doc']['actions']['type_property_add'][] = $params;
            }
        }

        // Remove types
       foreach (array_keys($currentTypeProperties) as $type) {

            if (!isset($definition[$type])) {
                $status[] = 'type removed in index [' . $type . '] ' . $elasticIndex;

                $params = array(
                    'index' => $elasticIndex,
                    'type' => $type
                );

                $typeStatus['_doc']['actions']['type_delete'][] = $params;
            }
        }

        if (!empty($status)) {
            $typeStatus['_doc']['error'] = true;
            $typeStatus['_doc']['status'] = implode(', ', $status);
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
                                // erLhcoreClassElasticClient::getHandler()->indices()->deleteMapping($params);
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
}