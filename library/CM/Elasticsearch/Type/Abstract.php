<?php

abstract class CM_Elasticsearch_Type_Abstract extends CM_Class_Abstract {

    const INDEX_NAME = null;

    const MAX_DOCS_PER_REQUEST = 1000;

    /** @var array */
    protected $_mapping = array();

    /** @var array */
    protected $_indexParams = array();

    /** @var bool */
    protected $_source = false;

    /** @var Elastica\Client */
    protected $_client = null;

    /** @var Elastica\Index */
    protected $_index = null;

    /** @var Elastica\Type */
    protected $_type = null;

    /**
     * @param string|null $host
     * @param string|null $port
     * @param int|null    $version
     * @throws CM_Exception_Invalid
     */
    public function __construct($host = null, $port = null, $version = null) {
        if (null === static::INDEX_NAME) {
            throw new CM_Exception_Invalid('Index name has to be set');
        }

        $indexName = CM_Bootloader::getInstance()->getDataPrefix() . static::INDEX_NAME;
        if ($version) {
            $indexName .= '.' . $version;
        }
        $typeName = static::INDEX_NAME;

        if (!$host || !$port) {
            $servers = CM_Config::get()->CM_Elasticsearch_Client->servers;
            $server = $servers[array_rand($servers)];
            $host = $server['host'];
            $port = $server['port'];
        }
        $this->_client = new Elastica\Client(array('host' => $host, 'port' => $port));

        $this->_index = new Elastica\Index($this->_client, $indexName);
        $this->_type = new Elastica\Type($this->_index, $typeName);
    }

    /**
     * @return Elastica\Index
     */
    public function getIndex() {
        return $this->_index;
    }

    /**
     * @return Elastica\Type
     */
    public function getType() {
        return $this->_type;
    }

    /**
     * @param  int|string $date
     * @return string
     */
    public function convertDate($date) {
        return Elastica\Util::convertDate($date);
    }

    /**
     * @param bool|null $recreate
     */
    public function create($recreate = null) {
        $this->getIndex()->create($this->_indexParams, $recreate);

        $mapping = new Elastica\Type\Mapping($this->getType(), $this->_mapping);
        $mapping->setSource(array('enabled' => $this->_source));
        $mapping->send();
    }

    public function createVersioned() {
        // Remove old unfinished indices
        foreach ($this->_client->getStatus()->getIndicesWithAlias($this->getIndex()->getName() . '.tmp') as $index) {
            /** @var Elastica\Index $index */
            $index->delete();
        }

        // Set current index to read-only
        foreach ($this->_client->getStatus()->getIndicesWithAlias($this->getIndex()->getName()) as $index) {
            $index->getSettings()->setBlocksWrite(true);
        }

        // Create new index and switch alias
        $version = time();
        /** @var $indexNew CM_Elasticsearch_Type_Abstract */
        $indexNew = new static($this->_client->getConfig('host'), $this->_client->getConfig('port'), $version);
        $indexNew->create(true);
        $indexNew->getIndex()->addAlias($this->getIndex()->getName() . '.tmp');

        $settings = $indexNew->getIndex()->getSettings();
        $refreshInterval = $settings->getRefreshInterval();
        //$mergeFactor = $settings->getMergePolicy('merge_factor');

        //$settings->setMergePolicy('merge_factor', 50);
        $settings->setRefreshInterval('-1');

        $indexNew->update(null, true);

        //$settings->setMergePolicy('merge_factor', $mergeFactor);
        $settings->setRefreshInterval($refreshInterval);

        $indexNew->getIndex()->addAlias($this->getIndex()->getName());
        $indexNew->getIndex()->removeAlias($this->getIndex()->getName() . '.tmp');

        // Remove old index
        foreach ($this->_client->getStatus()->getIndicesWithAlias($this->getIndex()->getName()) as $index) {
            /** @var Elastica\Index $index */
            if ($index->getName() != $indexNew->getIndex()->getName()) {
                $index->delete();
            }
        }
    }

    /**
     * Update the complete index
     *
     * @param mixed[]   $ids               Only update given IDs
     * @param bool|null $useSlave          Read data from one of the slave databases, if any
     * @param int       $limit             Limit query
     * @param int       $maxDocsPerRequest Number of docs per bulk-request
     */
    public function update($ids = null, $useSlave = null, $limit = null, $maxDocsPerRequest = self::MAX_DOCS_PER_REQUEST) {
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
        if ($useSlave) {
            $result = CM_Db_Db::execReadMaintenance($query, null, true);
        } else {
            $result = CM_Db_Db::exec($query);
        }

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
            if ($i++ % $maxDocsPerRequest == 0) {
                $this->_type->addDocuments($docs);
                $docs = array();
            }
        }

        // Add not yet sent documents to index
        if (!empty($docs)) {
            $this->_type->addDocuments($docs);
        }

        // Delete documents that were not updated (=not found)
        if (!empty($idsDelete)) {
            $idsDelete = array_keys($idsDelete);
            $this->getIndex()->getClient()->deleteIds($idsDelete, $this->getIndex()->getName(), $this->getType()->getName());
        }
    }

    /**
     * @param array $data
     * @return Elastica\Document Document with data
     */
    abstract protected function _getDocument(array $data);

    /**
     * @param array $ids
     * @param int   $limit
     * @return string SQL-query
     */
    abstract protected function _getQuery($ids = null, $limit = null);

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
        if (!CM_Elasticsearch_Client::getInstance()->getEnabled()) {
            return;
        }
        $id = self::getIdForItem($item);
        CM_Redis_Client::getInstance()->sAdd('Search.Updates_' . static::INDEX_NAME, (string) $id);
    }

    /**
     * @param mixed $item
     */
    public static function updateItemWithJob($item) {
        if (!CM_Elasticsearch_Client::getInstance()->getEnabled()) {
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
}
