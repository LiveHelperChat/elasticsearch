<?php

trait erLhcoreClassElasticTrait
{

    // Index name used for storage
    static $indexName = null;

    // Index name used for searching
    static $indexNameSearch = null;

    public function setState(array $properties)
    {
        foreach ($properties as $key => $val) {
            $this->$key = $val;
        }
    }

    public function saveThis()
    {
        $this->beforeSave();
        erLhcoreClassElasticClient::saveThis(self::getSession(), $this, (isset($this->meta_data['index']) ? $this->meta_data['index'] : self::$indexName), self::$elasticType);
        $this->afterSave();
        $this->clearCache();
    }

    public function updateThis()
    {
        $this->beforeUpdate();
        erLhcoreClassElasticClient::saveThis(self::getSession(), $this, (isset($this->meta_data['index']) ? $this->meta_data['index'] : self::$indexName), self::$elasticType);
        $this->afterUpdate();
        $this->clearCache();
    }

    public function removeThis()
    {
        $this->beforeRemove();
        erLhcoreClassElasticClient::removeObj(self::getSession(), $this, (isset($this->meta_data['index']) ? $this->meta_data['index'] : self::$indexName), self::$elasticType);
        $this->afterRemove();
        $this->clearCache();
    }

    public function removeThisOnly(){
        erLhcoreClassElasticClient::removeObj(self::getSession(), $this, (isset($this->meta_data['index']) ? $this->meta_data['index'] : self::$indexName), self::$elasticType);
    }

    public function beforeSave()
    {}

    public function beforeUpdate()
    {}

    public function beforeRemove()
    {}

    public function afterSave()
    {}

    public function afterUpdate()
    {}

    public function afterRemove()
    {}

    public function clearCache()
    {
        $cache = CSCacheAPC::getMem();
        $cache->increaseCacheVersion('site_attributes_version_' . strtolower(__CLASS__));
        $cache->delete('object_' . strtolower(__CLASS__) . '_' . $this->id);

        if (isset($GLOBALS[__CLASS__ . $this->id])) {
            unset($GLOBALS[__CLASS__ . $this->id]);
        }

        $this->clearCacheClassLevel();
    }

    public function clearCacheClassLevel()
    {}

    public static function getSession()
    {
        static $dbHandler = false;

        if ($dbHandler === false) {

            $settings = erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionElasticsearch')->settings;

            $dbHandler = erLhcoreClassElasticClient::getHandler();
            if (isset(self::$index) && self::$index != '') {
                $additional_indexes = $settings['additional_indexes'];
                if (isset($additional_indexes[self::$index])) {
                    self::$indexName = $additional_indexes[self::$index];
                } else {
                    throw new Exception(__t('elastic/message', 'No index set'));
                }
            } else {
                self::$indexName = $settings['index'];
                self::$indexNameSearch = $settings['index_search'];
            }
        }

        return $dbHandler;
    }

    public static function fetch($id, $indexName = null, $useCache = true)
    {
        if (isset($GLOBALS[__CLASS__ . $id]) && $useCache == true)
            return $GLOBALS[__CLASS__ . $id];

        try {
            $GLOBALS[__CLASS__ . $id] = erLhcoreClassElasticClient::load(self::getSession(), __CLASS__, $id, ($indexName === null ? self::$indexName : $indexName), self::$elasticType);
        } catch (Exception $e) {
            $GLOBALS[__CLASS__ . $id] = false;
        }

        return $GLOBALS[__CLASS__ . $id];
    }

    /**
     * Similar to above just uses memcache if available
     */
    public static function fetchCache($id)
    {
        $cache = CSCacheAPC::getMem();
        $cacheKey = 'object_' . strtolower(__CLASS__) . '_' . $id;

        if (($object = $cache->restore($cacheKey)) === false) {
            $object = self::fetch($id, true);
            $cache->store($cacheKey, $object);
        }

        return $object;
    }

    public static function findOne($paramsSearch = array(), $executionParams = array())
    {
        $paramsSearch['limit'] = 1;
        $list = self::getList($paramsSearch, $executionParams);
        if (! empty($list)) {
            reset($list);
            return current($list);
        }

        return false;
    }

    public static function getCount($params = array(), $executionParams = array())
    {
        if (isset($params['enable_sql_cache']) && $params['enable_sql_cache'] == true) {
            $sql = erLhcoreClassModuleFunctions::multi_implode(',', $params);

            $cache = CSCacheAPC::getMem();
            $cacheKey = isset($params['cache_key']) ? md5($sql . $params['cache_key']) : md5('objects_count_' . strtolower(__CLASS__) . '_v_' . $cache->getCacheVersion('site_attributes_version_' . strtolower(__CLASS__)) . $sql);

            if (($result = $cache->restore($cacheKey)) !== false) {
                return $result;
            }
        }

        if (isset($params['limit'])) {
            unset($params['limit']);
        }

        if (isset($params['offset'])) {
            unset($params['offset']);
        }

        if (isset($params['body']['highlight'])) {
            unset($params['body']['highlight']);
        }

        $searchHandler = self::getSession();

        $params['ignore_unavailable'] = true;

        $indexSearch = '';

        if (isset($executionParams['date_index']) && !empty($executionParams['date_index'])) {
            $indexSearch = self::extractIndexFilter($executionParams['date_index']);
        }

        $params['index'] = $indexSearch != '' ? $indexSearch : self::$indexNameSearch . '-' . self::$elasticType;

        $result = erLhcoreClassElasticClient::searchObjectsCount($searchHandler, $params);

        if (isset($params['enable_sql_cache']) && $params['enable_sql_cache'] == true) {
            $cache->store($cacheKey, $result);
        }

        return $result;
    }

    public static function extractIndexFilter($dataFilter, $indexName = null, $elasticType = null) {

        $esOptions = erLhcoreClassModelChatConfig::fetch('elasticsearch_options');
        $dataOptions = (array)$esOptions->data;

        if ($indexName == null) {
            $indexName = self::$indexName;
        }

        $indexSave = 'static';

        if (isset($dataOptions['index_type'])) {
            if ($dataOptions['index_type'] == 'daily') {
                $indexSave = 'daily';
            } elseif ($dataOptions['index_type'] == 'yearly') {
                $indexSave = 'yearly';
            } elseif ($dataOptions['index_type'] == 'monthly') {
                $indexSave = 'monthly';
            }
        }
        $indexes = array();

        if ($indexSave == 'static') {
            return '';
        }

        $starPrepend = '';
        if (isset($dataOptions['star_month_index']) && $dataOptions['star_month_index'] == 1){
            $starPrepend = '*';
        }

        if (isset($dataFilter['gte']) && !isset($dataFilter['lte'])) {

            $dataFilter['gte'] = $dataFilter['gte'] - (24*3600);

            $days = ceil((time()-$dataFilter['gte'])/(24*3600));

            if ($days < 31 && $indexSave == 'daily') {
                for ($i = 0; $i <= $days; $i++) {
                    $indexes[] = $indexName . '-' . ($elasticType == null ? self::$elasticType : $elasticType) . '-' . date('Y.m.d',$dataFilter['gte']+($i*24*3600));
                }
            } elseif ($indexSave == 'yearly') {
                $years = date('Y') - date('Y', $dataFilter['gte']);
                for ($i = 0; $i <= $years; $i++) {
                    $indexes[] = $indexName . '-' . ($elasticType == null ? self::$elasticType : $elasticType) . '-' . (date('Y', $dataFilter['gte']) +  $i) . ($indexSave == 'daily' ? '*' : $starPrepend);
                }
            } else {
                $months = ceil((time()-$dataFilter['gte'])/(28*24*3600)); // Use lowest possible month duration
                for ($i = 0; $i <= $months; $i++) {
                    $indexes[] = $indexName . '-' . ($elasticType == null ? self::$elasticType : $elasticType) . '-' . date('Y.m',$dataFilter['gte']+($i*28*24*3600)) . ($indexSave == 'daily' ? '*' : $starPrepend);
                }
            }


        } elseif (isset($dataFilter['gte']) && isset($dataFilter['lte'])){

            $dataFilter['gte'] = $dataFilter['gte'] - (24*3600);
            $dataFilter['lte'] = $dataFilter['lte'] + (24*3600);

            $days = ceil(($dataFilter['lte']-$dataFilter['gte'])/(24*3600));

            if ($days < 31 && $indexSave == 'daily') {
                for ($i = 0; $i <= $days; $i++) {
                    $indexes[] = $indexName . '-' . ($elasticType == null ? self::$elasticType : $elasticType) . '-' . date('Y.m.d',$dataFilter['gte']+($i*24*3600));
                }
            } elseif ($indexSave == 'yearly') {
                $years = date('Y',$dataFilter['lte']) - date('Y', $dataFilter['gte']);
                for ($i = 0; $i <= $years; $i++) {
                    $indexes[] = $indexName . '-' . ($elasticType == null ? self::$elasticType : $elasticType) . '-' . (date('Y', $dataFilter['gte']) +  $i) . ($indexSave == 'daily' ? '*' : $starPrepend);
                }
            } else {
                $months = ceil(($dataFilter['lte']-$dataFilter['gte'])/(28*24*3600)); // Use lowest possible month duration
                for ($i = 0; $i <= $months; $i++) {
                    $indexes[] = $indexName . '-' . ($elasticType == null ? self::$elasticType : $elasticType) . '-' .date('Y.m',$dataFilter['gte']+($i*28*24*3600)) . ($indexSave == 'daily' ? '*' : $starPrepend);
                }
            }
        }

        if (!empty($indexes)) {
            $indexes[] = $indexName;
        }

        return implode(',',array_unique($indexes));
    }

    public static function getList($params = array(), $executionParams = array())
    {
        $paramsDefault = array(
            'limit' => 1000,
            'offset' => 0
        );

        $params = array_merge($paramsDefault, $params);

        if (isset($params['enable_sql_cache']) && $params['enable_sql_cache'] == true) {
            $sql = erLhcoreClassModuleFunctions::multi_implode(',', $params);

            $cache = CSCacheAPC::getMem();
            $cacheKey = isset($params['cache_key']) ? md5($sql . $params['cache_key']) : md5('objects_list_' . strtolower(__CLASS__) . '_v_' . $cache->getCacheVersion('site_attributes_version_' . strtolower(__CLASS__)) . $sql);

            if (($result = $cache->restore($cacheKey)) !== false) {
                return $result;
            }
        }

        $searchHandler = self::getSession();

        $indexSearch = '';

        if (isset($executionParams['date_index']) && !empty($executionParams['date_index'])) {
            $indexSearch = self::extractIndexFilter($executionParams['date_index']);
        }

        $params['index'] = $indexSearch != '' ? $indexSearch : self::$indexNameSearch . '-' . self::$elasticType;

        $params['ignore_unavailable'] = true;

        // Convert pagination parameters to elastic one
        if (isset($params['limit'])) {
            if ($params['limit'] > 0) {
                $params['size'] = $params['limit'];
            }
            unset($params['limit']);
        }

        if (isset($params['offset'])) {
            if ($params['offset'] > 0) {
                $params['from'] = $params['offset'];
            }
            unset($params['offset']);
        }

        $objects = erLhcoreClassElasticClient::searchObjects($searchHandler, $params, __CLASS__);

        if (isset($params['enable_sql_cache']) && $params['enable_sql_cache'] == true) {
            if (isset($params['sql_cache_timeout'])) {
                $cache->store($cacheKey, $objects, $params['sql_cache_timeout']);
            } else {
                $cache->store($cacheKey, $objects);
            }
        }

        return $objects;
    }
    
    public static function getAggregation($params, $executionParams, $aggregationName)
    {
        $paramsDefault = array(
            'limit' => 0
        );

        $params = array_merge($paramsDefault, $params);

        $searchHandler = self::getSession();

        $indexSearch = '';

        if (isset($executionParams['date_index']) && !empty($executionParams['date_index'])) {
            $indexSearch = self::extractIndexFilter($executionParams['date_index']);
        }

        $params['index'] = $indexSearch != '' ? $indexSearch : self::$indexNameSearch . '-' . self::$elasticType;

        $params['ignore_unavailable'] = true;

        // Convert pagination parameters to elastic one
        if (isset($params['limit'])) {
            if ($params['limit'] >= 0) {
                $params['size'] = $params['limit'];
            }
            unset($params['limit']);
        }

        $objects = erLhcoreClassElasticClient::searchObjectsAggregated($searchHandler, $params, __CLASS__, $aggregationName);

        if (isset($params['enable_sql_cache']) && $params['enable_sql_cache'] == true) {
            if (isset($params['sql_cache_timeout'])) {
                $cache->store($cacheKey, $objects, $params['sql_cache_timeout']);
            } else {
                $cache->store($cacheKey, $objects);
            }
        }

        return $objects;
    }


    public static function mGet($ids)
    {
        $searchHandler = self::getSession();

        $params['index'] = self::$indexName;
        $params['body']['ids'] = array_values($ids);

        return erLhcoreClassElasticClient::mGet($searchHandler, $params, __CLASS__);
    }

    public static function bulkSave(& $objects, $paramsExecution = array())
    {
        $searchHandler = self::getSession();

        if (!isset($paramsExecution['custom_index']))
        {
            $params['index'] = self::$indexName;

            $operations = array();

            foreach ($objects as $object) {
                if ($object->id == null) {
                    $object->beforeSave();
                    $operations[] = "{ \"index\":  {} }";
                } else {
                    $object->beforeUpdate();
                    $operations[] = '{ "index":  { "_id": "' . $object->id . '"} }';
                }
                $state = $object->getState();
                if (isset($paramsExecution['ignore_id'])) {
                    unset($state['id']);
                }
                $operations[] = json_encode($state);
            }

            if (! empty($operations)) {
                $operations[] = "";
                $params['body'] = implode("\n", $operations);
                return erLhcoreClassElasticClient::bulkSave($searchHandler, $params, $objects, $paramsExecution);
            }

        } else {

            foreach ($objects as $index => $objectCollection)
            {
                $params['index'] = $index;
                $operations = array();
                foreach ($objectCollection as $object) {
                    if ($object->id == null) {
                        $object->beforeSave();
                        $operations[] = "{ \"index\":  {} }";
                    } else {
                        $object->beforeUpdate();
                        $operations[] = '{ "index":  { "_id": "' . $object->id . '"} }';
                    }
                    $state = $object->getState();
                    if (isset($paramsExecution['ignore_id'])) {
                        unset($state['id']);
                    }
                    $operations[] = json_encode($state);
                }

                if (! empty($operations)) {
                    $operations[] = "";
                    $params['body'] = implode("\n", $operations);

                    erLhcoreClassElasticClient::bulkSave($searchHandler, $params, $objectCollection, $paramsExecution);
                }
            }
        }
    }

    public static function bulkDelete(& $objects, $paramsExecution = array())
    {
        $searchHandler = self::getSession();

        $params['index'] = self::$indexName;

        $operations = array();

        foreach ($objects as $object) {
            if ($object->id != null) {
                $object->beforeRemove();
                $operations[] = '{ "delete":  { "_id": "' . $object->id . '"} }';
            }
        }

        if (! empty($operations)) {
            $operations[] = "";
            $params['body'] = implode("\n", $operations);
            return erLhcoreClassElasticClient::bulkSave($searchHandler, $params, $objects, $paramsExecution);
        }
    }
}

?>
