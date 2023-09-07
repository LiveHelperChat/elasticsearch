<?php

use Aws\Credentials\CredentialProvider;
use Aws\Signature\SignatureV4;
use Elasticsearch\ClientBuilder;
use GuzzleHttp\Ring\Future\CompletedFutureArray;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\RequestOptions;
#[\AllowDynamicProperties]
class erLhcoreClassElasticClient
{

    private static $handler = null;

    public static function saveThis($handler, &$obj, $indexName, $indexType)
    {

        $updateParams = array();
        $updateParams['index'] = $indexName;

        try {
            if ($obj->id !== null) {
                $updateParams['id'] = $obj->id;
                $updateParams['body']['doc'] = $obj->getState();
                $handler->update($updateParams);
            } else {
                $updateParams['body'] = $obj->getState();
                $ret = $handler->index($updateParams);
                $obj->id = $ret['_id'];
            }
        } catch (Exception $e) {
            if (!isset($handler->throwExceptionOnFailure) || $handler->throwExceptionOnFailure === true) {
                throw $e;
            }
        }
    }

    public static $lastSearchCount = 0;

    public static function searchObjects($handler, $params, $className)
    {

        if (isset($params['enable_sql_cache'])) {
            unset($params['enable_sql_cache']);
        }

        if (isset($params['sql_cache_timeout'])) {
            unset($params['sql_cache_timeout']);
        }

        self::$lastSearchCount = 0;

        $response = $handler->search($params);

        $returnObjects = array();
        if (isset($response['hits']['hits']) && !empty($response['hits']['hits'])) {
            foreach ($response['hits']['hits'] as $doc) {
                $obj = new $className();
                $obj->setState($doc['_source']);
                $obj->id = $doc['_id'];

                $metaData = array('score' => $doc['_score'], 'index' => $doc['_index']);
                if (isset($doc['highlight'])) {
                    $metaData['highlight'] = $doc['highlight'];
                }

                $obj->meta_data = $metaData;
                $returnObjects[$obj->id] = $obj;
            }

            self::$lastSearchCount = $response['hits']['total'];
        }

        return $returnObjects;
    }
    
    public static function searchObjectsAggregated($handler, $params, $className, $aggregationName)
    {

        if (isset($params['enable_sql_cache'])) {
            unset($params['enable_sql_cache']);
        }

        if (isset($params['sql_cache_timeout'])) {
            unset($params['sql_cache_timeout']);
        }

        self::$lastSearchCount = 0;

        $response = $handler->search($params);

        $returnObjects = array();
        if(isset($response['aggregations'][$aggregationName]['buckets']) && !empty($response['aggregations'][$aggregationName]['buckets'])){
            
            $count = count($response['aggregations'][$aggregationName]['buckets']);
            foreach($response['aggregations'][$aggregationName]['buckets'] as $element){  
                $obj = new $className();
                $obj->aggregationName = $aggregationName;
                $obj->setState($element);
                
                $returnObjects[] = $obj;
            }
            self::$lastSearchCount = $count;
        }
        
        return $returnObjects;
    }

    public static function mGet($handler, $params, $className)
    {
        $documents = $handler->mget($params);

        $returnObjects = array();

        foreach ($documents['docs'] as $doc) {
            if ($doc['found'] == 1) {
                $obj = new $className();
                $obj->setState($doc['_source']);
                $obj->id = $doc['_id'];
                $returnObjects[$obj->id] = $obj;
            }
        }

        return $returnObjects;
    }

    public static function indexExists($handler, $index, $indexPrepend = null, $forceUpdate = false)
    {
        static $indexChecked = array();

        $contentData = file_get_contents('extension/elasticsearch/doc/structure_elastic.json');

        $settings = erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionElasticsearch')->settings;

        $contentData = array($index => json_decode($contentData, true));

        erLhcoreClassChatEventDispatcher::getInstance()->dispatch('system.getelasticstructure_core', array(
            'structure' => & $contentData,
            'index_original' => $settings['index'],
            'index_new' => $index,
        ));

        foreach ($contentData[$index]['types'] as $type => $mapping) {

            $indexCurrent = $index . '-' . $type . ($indexPrepend != null ? '-' . $indexPrepend : '');

            if (in_array($indexCurrent, $indexChecked)) {
                continue;
            }

            $indexChecked[] = $indexCurrent;

            if (($indexExists = $handler->indices()->exists(array('index' => $indexCurrent))) == false || $forceUpdate == true) {

                if ($indexExists == false) {
                    $handler->indices()->create(array(
                        'index' => $indexCurrent
                    ));
                }

                erLhcoreClassChatEventDispatcher::getInstance()->dispatch('system.getelasticstructure', array(
                    'structure' => & $contentData,
                    'index_original' => $settings['index'],
                    'index_new' => $index,
                    'type' => $type,
                    'mapping' => & $mapping,
                ));

                if (isset($contentData[$index]['types'][$type])) {
                    $mapping = array_merge($mapping, $contentData[$index]['types'][$type]);
                }

                if ($type == 'lh_chat' && isset($settings['columns']) && !empty($settings['columns'])) {
                   foreach ($settings['columns'] as $field => $dataField) {
                       if (isset($dataField['type'])) {
                           $mapping[$field] = [
                               'type' => $dataField['type']
                           ];
                       }
                   }
                }

                erLhcoreClassElasticSearchUpdate::doElasticUpdate($mapping, $indexCurrent);
            }
        }
    }

    public static function bulkSave($handler, $params, &$objects, $paramsExecution = array())
    {
        //$indexParams = explode('-', $params['index']);
        //self::indexExists($handler, $indexParams[0], (isset($indexParams[2]) ? $indexParams[2] : null));

        $action = $handler->bulk($params);

        if (erConfigClassLhConfig::getInstance()->getSetting('site', 'debug_output') == true) {
            //erLhcoreClassLog::write(print_r(array('log' => json_encode($params) . ' -------- ' . json_encode($action), 'function' => 'bulk_save'),true));
        }

        if (!empty($action['items'])) {
            foreach ($objects as $key => & $object) {
                if (isset($action['items'][$key])) {
                    if ($object->id == null) {
                        $object->id = $action['items'][$key]['index']['_id'];
                        $object->afterSave();
                    } else {
                        $object->afterUpdate();
                    }

                    // clear cache only if needed
                    if (isset($paramsExecution['clear_cache']) && $paramsExecution['clear_cache'] == true) {
                        $object->clearCache();
                    }
                }
            }

            // Just to clear class cache once
            $object->clearCache();
        }

        // Log bulk save errors
        if (isset($action['errors']) && $action['errors'] == true) {
            erLhcoreClassLog::write(print_r(array('log' => json_encode($action), 'function' => 'bulk_save_error'), true));
        }

        return $action;
    }

    public static function searchObjectsCount($handler, $params)
    {

        if (isset($params['enable_sql_cache'])) {
            unset($params['enable_sql_cache']);
        }

        if (isset($params['sql_cache_timeout'])) {
            unset($params['sql_cache_timeout']);
        }

        $response = $handler->count($params);

        $documentsCount = 0;
        if (isset($response['count'])) {
            $documentsCount = $response['count'];
        }

        return $documentsCount;
    }

    public static function removeObj($handler, &$obj, $indexName, $indexType)
    {

        $deleteParams = array();
        $deleteParams['index'] = $indexName;
        $deleteParams['id'] = $obj->id;
        $handler->delete($deleteParams);
    }

    public static function load($handler, $className, $id, $indexName, $indexType)
    {

        $getParams = array();
        $getParams['index'] = $indexName;
        $getParams['id'] = $id;

        $retDoc = $handler->get($getParams);

        if (isset($retDoc['found']) && $retDoc['found'] == 1) {
            $obj = new $className();
            $obj->setState($retDoc['_source']);
            $obj->id = $retDoc['_id'];
            $obj->meta_data = array('index' => $retDoc['_index']);
            return $obj;
        } else {
            throw new Exception($className . ' with id ' . $id . ' [' . $indexName . '][' . $indexType . '] could not be found!');
        }
    }

    public static function getHandler()
    {

        if (is_null(self::$handler)) {

            $settings = erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionElasticsearch')->settings;

            $elasticClient = Elasticsearch\ClientBuilder::create();

            if ($settings['use_iam'] == true) {

                $psr7Handler = Aws\default_http_handler();
                $signer = new SignatureV4('es', $settings['iam_region']);
                $credentialProvider = CredentialProvider::defaultProvider();

                // Construct the handler that will be used by Elasticsearch-PHP
                $handler = function (array $request) use (
                    $psr7Handler,
                    $signer,
                    $credentialProvider
                ) {
                    // Amazon ES listens on standard ports (443 for HTTPS, 80 for HTTP).
                    $request['headers']['host'][0] = parse_url($request['headers']['host'][0], PHP_URL_HOST);

                    // Create a PSR-7 request from the array passed to the handler
                    $psr7Request = new Request(
                        $request['http_method'],
                        (new Uri($request['uri']))
                            ->withScheme($request['scheme'])
                            ->withHost($request['headers']['host'][0]),
                        $request['headers'],
                        $request['body']
                    );

                    // Sign the PSR-7 request with credentials from the environment
                    $signedRequest = $signer->signRequest(
                        $psr7Request,
                        call_user_func($credentialProvider)->wait()
                    );

                    // Send the signed request to Amazon ES
                    /** @var ResponseInterface $response */
                    $response = $psr7Handler($signedRequest)->wait();

                    // Convert the PSR-7 response to a RingPHP response
                    return new CompletedFutureArray([
                        'status' => $response->getStatusCode(),
                        'headers' => $response->getHeaders(),
                        'body' => $response->getBody()->detach(),
                        'transfer_stats' => ['total_time' => 0],
                        'effective_url' => (string)$psr7Request->getUri(),
                    ]);
                };

                $elasticClient->setHandler($handler);
            }

            self::$handler = $elasticClient->setHosts(array($settings['host'] . ':' . $settings['port']))->setConnectionParams([
                'client' => [
                    RequestOptions::TIMEOUT => (erLhcoreClassSystem::instance()->backgroundMode == true ? 15 : 10),
                    RequestOptions::CONNECT_TIMEOUT => (erLhcoreClassSystem::instance()->backgroundMode == true ? 10 : 2),
                ],
            ])->build();
        }

        return self::$handler;
    }

    public static function deleteAllDocumentsByType($handler, $indexName, $indexType)
    {

        $params = array();
        $params['index'] = $indexName;
        $params['body']['query'] = array('match_all' => array());

        $handler->deleteByQuery($params);
    }
}

?>
