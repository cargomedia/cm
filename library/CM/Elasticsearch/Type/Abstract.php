<?php

abstract class CM_Elasticsearch_Type_Abstract extends CM_Class_Abstract implements CM_Elasticsearch_AliasInterface {

    const MAX_DOCS_PER_REQUEST = 1000;

    /** @var array */
    protected $_mapping = array();

    /** @var array */
    protected $_indexParams = array();

    /** @var bool */
    protected $_source = false;

    /** @var CM_Elasticsearch_Client */
    protected $_client;

    /** @var string */
    protected $_indexName = null;

    /** @var string */
    protected $_typeName = null;

    /**
     * @param CM_Elasticsearch_Client $client
     * @param int|null             $version
     * @throws CM_Exception_Invalid
     */
    public function __construct(CM_Elasticsearch_Client $client, $version = null) {
        $this->_indexName = $this->_buildIndexName($version);
        $this->_typeName = static::getAliasName();
        $this->_client = $client;
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
     * @return CM_Elasticsearch_Client
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

    public function createIndex() {
        $client = $this->getClient();
        $tempAliasName = $this->getIndexName() . '.tmp';

        // Remove old unfinished indices
        $unfinishedIndexList = $client->getIndexesByAlias($tempAliasName);
        $client->deleteIndex($unfinishedIndexList);

        // Set current index to read-only
        $currentIndexList = $client->getIndexesByAlias($this->getIndexName());
        if (!empty($currentIndexList)) {
            $client->putIndexSettings($currentIndexList, ['blocks.write' => 1]);
        }

        // Create new index and switch alias
        $indexCreatedName = $this->_buildIndexName(time());

        $client->deleteIndex($indexCreatedName);
        $client->createIndex($indexCreatedName, $this->getTypeName(), $this->_indexParams, $this->_mapping, $this->_source);
        $client->putAlias($indexCreatedName, $tempAliasName);

        //save refresh_interval
        $refreshInterval = $client->getIndexSettings($this->getIndexName(), 'refresh_interval');
        if (null === $refreshInterval) {
            $refreshInterval = '1s';
        }

        //temporary disable refresh_interval during documents updating and then put it back
        $client->putIndexSettings($indexCreatedName, ['refresh_interval' => '-1']);
        $this->_updateDocuments($indexCreatedName, null, true);
        $client->putIndexSettings($indexCreatedName, ['refresh_interval' => $refreshInterval]);

        //switch aliases
        $client->putAlias($indexCreatedName, $this->getIndexName());
        $client->deleteAlias($indexCreatedName, $tempAliasName);

        // Remove old index
        $oldIndexList = $client->getIndexesByAlias($this->getIndexName());
        $oldIndexList = array_filter($oldIndexList, function ($el) use ($indexCreatedName) {
            return ($el !== $indexCreatedName);
        });
        $client->deleteIndex($oldIndexList);
    }

    /**
     * @return int
     */
    public function count() {
        return $this->getClient()->count($this->getIndexName(), $this->getTypeName());
    }

    /**
     * @return bool
     */
    public function indexExists() {
        return $this->getClient()->indexExists($this->getIndexName());
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
        $this->getClient()->deleteIndex($this->getIndexName());
    }

    public function refreshIndex() {
        $this->getClient()->refreshIndex($this->getIndexName());
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
                $this->getClient()->bulkAddDocuments($docs, $indexName, $this->getTypeName());
                $docs = [];
            }
        }

        // Add not yet sent documents to index
        if (!empty($docs)) {
            $this->getClient()->bulkAddDocuments($docs, $indexName, $this->getTypeName());
        }

        // Delete documents that were not updated (=not found)
        if (!empty($idsDelete)) {
            $idsDelete = array_keys($idsDelete);
            $this->getClient()->bulkDeleteDocuments($idsDelete, $indexName, $this->getTypeName());
        }
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
        $serviceManager = CM_Service_Manager::getInstance();
        if (!$serviceManager->getElasticsearch()->getEnabled()) {
            return;
        }
        $job = new CM_Elasticsearch_UpdateDocumentJob(CM_Params::factory([
            'indexClassName' => get_called_class(),
            'id'             => static::getIdForItem($item),
        ]));
        $serviceManager->getJobQueue()->queue($job);
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
}
