<?php

abstract class CM_Elasticsearch_Type_Abstract extends CM_Class_Abstract implements CM_Elasticsearch_Alias {

    const MAX_DOCS_PER_REQUEST = 1000;

    /** @var array */
    protected $_mapping = array();

    /** @var array */
    protected $_indexParams = array();

    /** @var bool */
    protected $_source = false;

    /** @var Elasticsearch\Client */
    protected $_client = null;

    /** @var string */
    protected $_indexName = null;

    /** @var string */
    protected $_typeName = null;

    /**
     * @param Elasticsearch\Client $client
     * @param int|null             $version
     * @throws CM_Exception_Invalid
     */
    public function __construct(Elasticsearch\Client $client, $version = null) {
        $this->_indexName = $this->_buildIndexName($version);
        $this->_typeName = static::getAliasName();
        $this->_client = $client; //TODO maybe make a facade for it
    }

    /**
     * @param array $data
     * @return CM_Elasticsearch_Document Document with data
     */
    abstract protected function _getDocument(array $data);

    /**
     * @param array $ids
     * @param int   $limit
     * @return string SQL-query
     */
    abstract protected function _getQuery($ids = null, $limit = null);

    /**
     * @return string
     */
    public function getIndex() {
        return $this->_indexName;
    }
    //TODO remove, now left for searching

    /**
     * @return Elasticsearch\Client|null
     */
    public function getClient() {
        return $this->_client;
    }

    /**
     * @return string
     */
    public function getIndexName() {
        return $this->_indexName;
    }

    /**
     * @return string
     */
    public function getTypeName() {
        return $this->_typeName;
    }

    /**
     * @return bool
     */
    public function indexExists() {
        return $this->getClient()->indices()->exists(['index' => $this->getIndexName()]);
    }

    public function createIndex() {
        $indicesNamespace = $this->getClient()->indices();

        // Remove old unfinished indices
        $unfinishedIndexList = $this->_getIndexesByAlias($this->getIndexName() . '.tmp');
        $this->_deleteIndex($unfinishedIndexList);

        // Set current index to read-only
        $currentIndexList = $this->_getIndexesByAlias($this->getIndexName());
        if (!empty($currentIndexList)) {
            $this->_putIndexSettings($currentIndexList, ['index.blocks.write' => 1]);
        }

        // Create new index and switch alias
        $indexCreatedName = $this->_buildIndexName(time());

        $this->_createIndex($indexCreatedName, true);

        $this->_putAlias($indexCreatedName, $this->getIndexName() . '.tmp');

        //save refresh_interval
        $refreshInterval = $this->_getIndexSettings($this->getIndexName(), 'refresh_interval');
        if (null === $refreshInterval) {
            $refreshInterval = '1s';
        }

        $this->_putIndexSettings($indexCreatedName, ['refresh_interval' => -1]);

        $this->_updateDocuments($indexCreatedName, null, true);

        $this->_putIndexSettings($indexCreatedName, ['refresh_interval' => $refreshInterval]);

        $this->_putAlias($indexCreatedName, $this->getIndexName());
        $indicesNamespace->deleteAlias([
            'index' => $indexCreatedName,
            'name'  => $this->getIndexName() . '.tmp',
        ]);

        // Remove old index
        $oldIndexList = $this->_getIndexesByAlias($this->getIndexName());
        $oldIndexList = array_filter($oldIndexList, function ($el) use ($indexCreatedName) {
            return ($el !== $indexCreatedName);
        });
        $this->_deleteIndex($oldIndexList);
    }

    /**
     * @throws CM_Exception_Invalid
     */
    public function updateIndex() {
        $redis = CM_Service_Manager::getInstance()->getRedis();
        $indexName = $this->getIndexName();
        $key = 'Search.Updates_' . $this->getTypeName();
        try {
            $ids = $redis->sFlush($key);
            $ids = array_filter(array_unique($ids));
            $this->updateDocuments($ids);
            $this->refreshIndex();
        } catch (Exception $e) {
            $message = $indexName . '-updates failed.' . PHP_EOL;
            if (isset($ids)) {
                $message .= 'Re-adding ' . count($ids) . ' ids to queue.' . PHP_EOL;
                foreach ($ids as $id) {
                    $redis->sAdd($key, $id);
                }
            }
            $message .= 'Reason: ' . $e->getMessage() . PHP_EOL;
            throw new CM_Exception_Invalid($message);
        }
    }

    public function deleteIndex() {
        $this->_deleteIndex($this->getIndexName());
    }

    public function refreshIndex() {
        $this->_refreshIndex($this->getIndexName());
    }

    /**
     * Update the complete index
     *
     * @param mixed[]   $ids               Only update given IDs
     * @param bool|null $useMaintenance    Read data from the maintenance database, if any
     * @param int       $limit             Limit query
     * @param int       $maxDocsPerRequest Number of docs per bulk-request
     */
    public function updateDocuments($ids = null, $useMaintenance = null, $limit = null, $maxDocsPerRequest = self::MAX_DOCS_PER_REQUEST) {
        $this->_updateDocuments($this->getIndexName(), $ids, $useMaintenance, $limit, $maxDocsPerRequest);
    }

    /**
     * @param  Datetime|int $date
     * @return string
     * @throws CM_Exception_Invalid
     */
    public function convertDate($date) {
        if ($date instanceof DateTime) {
            $timestamp = $date->getTimestamp();
        } elseif (is_int($date)) {
            $timestamp = $date;
        } else {
            throw new CM_Exception_Invalid('convertDate argument should be integer or DateTime');
        }
        return date('Y-m-d\TH:i:s\Z', $timestamp);
    }

    /**
     * @param string[]|string $indexName
     * @param string|null     $settingKey
     * @return mixed|null
     * @throws CM_Exception_Invalid
     */
    protected function _getIndexSettings($indexName, $settingKey = null) {
        $paramIndex = self::_prepareIndexNameParam($indexName);
        if ('' === $paramIndex) {
            throw new CM_Exception_Invalid('Invalid elasticsearch index value');
        }
        $settingsResponse = $this->getClient()->indices()->getSettings([
            'index'  => $paramIndex,
            'client' => ['ignore' => 404],
        ]);

        if (null !== $settingKey) {
            $settingKey = (string) $settingKey;
            $settingsList = current($settingsResponse); //{"photo.1441893401":{"settings":{"index":{"blocks":{"write":"0"},...
            if (isset($settingsList['settings']['index'][$settingKey])) {
                return $settingsList['settings']['index'][$settingKey];
            } else {
                return null;
            }
        } else {
            return $settingsResponse;
        }
    }

    /**
     * @param string[]|string $indexName
     * @param array           $settings
     * @throws CM_Exception_Invalid
     */
    protected function _putIndexSettings($indexName, array $settings) {
        $paramIndex = self::_prepareIndexNameParam($indexName);
        if ('' === $paramIndex) {
            throw new CM_Exception_Invalid('Invalid elasticsearch index value');
        }

        $this->getClient()->indices()->putSettings([
            'index' => $paramIndex,
            'body'  => [
                'settings' => $settings,
            ]
        ]);
    }

    /**
     * @param string $indexName
     * @param string $aliasName
     */
    protected function _putAlias($indexName, $aliasName) {
        $this->getClient()->indices()->putAlias([
            'index' => (string) $indexName,
            'name'  => (string) $aliasName,
        ]);
    }

    /**
     * @param string|null $version
     * @return string
     */
    protected function _buildIndexName($version = null) {
        $indexName = CM_Bootloader::getInstance()->getDataPrefix() . static::getAliasName();
        if ($version) {
            $indexName .= '.' . $version;
        }
        return $indexName;
    }

    /**
     * @param string    $newIndexName
     * @param bool|null $recreate
     */
    protected function _createIndex($newIndexName, $recreate = null) {
        $newIndexName = (string) $newIndexName;
        $indexHandler = $this->getClient()->indices();
        if (true === $recreate) {
            $this->_deleteIndex($newIndexName);
        }

        $indexParams = $this->_indexParams;
        if (!empty($indexParams['index'])) {
            foreach ($indexParams['index'] as $settingKey => $settingValue) {
                $indexParams[$settingKey] = $settingValue;
            }
            unset($indexParams['index']);
        }
        //Different index settings params
        //TODO either fix it on all indices or create adapter method

        $requestParams = [
            'index' => $newIndexName,
            'body'  => [
                'settings' => $indexParams,
            ]
        ];

        if (!empty($this->_mapping)) {
            $requestParams['body']['mappings'][$this->getTypeName()] = [
                '_source'    => [
                    'enabled' => (bool) $this->_source,
                ],
                'properties' => $this->_mapping,
            ];
        }

        $indexHandler->create($requestParams);
    }

    /**
     * @param string[]|string $indexName
     */
    protected function _deleteIndex($indexName) {
        $paramIndex = self::_prepareIndexNameParam($indexName);
        if ('' !== $paramIndex) {
            $this->getClient()->indices()->delete([
                'index'  => $paramIndex,
                'client' => ['ignore' => 404],
            ]);
        }
    }

    /**
     * @param string[]|string $indexName
     * @throws CM_Exception_Invalid
     */
    protected function _refreshIndex($indexName) {
        $paramIndex = self::_prepareIndexNameParam($indexName);
        if ('' === $paramIndex) {
            throw new CM_Exception_Invalid('Invalid elasticsearch index value');
        }

        $this->getClient()->indices()->refresh([
            'index' => $paramIndex,
        ]);
    }

    /**
     * @param      $indexName
     * @param null $ids
     * @param null $useMaintenance
     * @param null $limit
     * @param int  $maxDocsPerRequest
     * @throws CM_Db_Exception
     */
    protected function _updateDocuments($indexName, $ids = null, $useMaintenance = null, $limit = null, $maxDocsPerRequest = self::MAX_DOCS_PER_REQUEST) {
        if (is_array($ids) && empty($ids)) {
            return;
        }
        if (is_array($ids)) {
            $idsDelete = array();
            foreach ($ids as $id) {
                $idsDelete[$id] = true;
            }
        }
        $query = $this->_getQuery($ids, $limit);
        if ($useMaintenance) {
            $client = CM_Service_Manager::getInstance()->getDatabases()->getReadMaintenance();
            $disableQueryBuffering = true;
        } else {
            $client = CM_Service_Manager::getInstance()->getDatabases()->getMaster();
            $disableQueryBuffering = false;
        }
        $result = $client->createStatement($query)->execute(null, $disableQueryBuffering);

        $docs = array();
        $i = 0;
        // Loops through all results. Write every $maxDocsPerRequest docs to the server
        while ($row = $result->fetch()) {
            $doc = $this->_getDocument($row);
            $docs[] = $doc;

            if (!empty($idsDelete)) {
                unset($idsDelete[$doc->getId()]);
            }

            // Add documents to index and empty documents array
            if (++$i % $maxDocsPerRequest == 0) {
                $this->_bulkAddDocuments($docs, $indexName, $this->getTypeName());
                $docs = [];
            }
        }

        // Add not yet sent documents to index
        if (!empty($docs)) {
            $this->_bulkAddDocuments($docs, $indexName, $this->getTypeName());
        }

        // Delete documents that were not updated (=not found)
        if (!empty($idsDelete)) {
            $idsDelete = array_keys($idsDelete);
            $this->_bulkDeleteDocuments($idsDelete, $indexName, $this->getTypeName());
        }
    }

    /**
     * @param array  $idsDelete
     * @param string $indexName
     * @param string $typeName
     */
    protected function _bulkDeleteDocuments(array $idsDelete, $indexName, $typeName) {
        $requestBody = [];
        foreach ($idsDelete as $id) {
            $requestBody[] = ['delete' => ['_id' => (string) $id]];
        }
        $this->getClient()->bulk([
            'index' => $indexName,
            'type'  => $typeName,
            'body'  => $requestBody,
        ]);
    }

    /**
     * @param CM_Elasticsearch_Document[] $documentList
     * @param string                      $indexName
     * @param string                      $typeName
     */
    protected function _bulkAddDocuments(array $documentList, $indexName, $typeName) {
        $requestBody = [];

        foreach ($documentList as $document) {
            $createParams = [];
            $documentId = $document->getId();
            if (null !== $documentId) {
                $createParams = ['_id' => $documentId];
            }
            $requestBody[] = ['index' => $createParams];
            $requestBody[] = $document->toArray();
        }
        $this->getClient()->bulk([
            'index' => $indexName,
            'type'  => $typeName,
            'body'  => $requestBody,
        ]);
    }

    /**
     * @param string $alias
     * @return string[]
     */
    protected function _getIndexesByAlias($alias) {
        $indices = $this->getClient()->indices();
        try {
            $response = $indices->getAlias([
                'name' => (string) $alias,
            ]);
        } catch (\Elasticsearch\Common\Exceptions\Missing404Exception $e) {
            $response = [];
        }
        return array_keys($response);
    }

    /**
     * @param mixed $item
     * @return string
     */
    public static function getIdForItem($item) {
        return static::_getIdSerialized(static::_getIdForItem($item));
    }

    /**
     * @param mixed $item
     */
    public static function updateItem($item) {
        if (!CM_Service_Manager::getInstance()->getElasticsearch()->getEnabled()) {
            return;
        }
        $id = self::getIdForItem($item);
        $redis = CM_Service_Manager::getInstance()->getRedis();
        $redis->sAdd('Search.Updates_' . static::getAliasName(), (string) $id);
    }

    /**
     * @param mixed $item
     */
    public static function updateItemWithJob($item) {
        if (!CM_Service_Manager::getInstance()->getElasticsearch()->getEnabled()) {
            return;
        }
        $job = new CM_Elasticsearch_UpdateDocumentJob();
        $job->queue(array(
            'indexClassName' => get_called_class(),
            'id'             => static::getIdForItem($item),
        ));
    }

    /**
     * @param mixed $item
     * @return mixed
     * @throws CM_Exception_NotImplemented
     */
    protected static function _getIdForItem($item) {
        throw new CM_Exception_NotImplemented();
    }

    /**
     * @param mixed $id
     * @return string
     */
    protected static function _getIdSerialized($id) {
        if (is_scalar($id)) {
            return (string) $id;
        }
        return CM_Params::encode($id, true);
    }

    /**
     * @param string[]|string $indexName
     * @return string
     */
    protected static function _prepareIndexNameParam($indexName) {
        return is_array($indexName) ? join(',', $indexName) : (string) $indexName;
    }
}
