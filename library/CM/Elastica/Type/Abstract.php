<?php

abstract class CM_Elastica_Type_Abstract extends Elastica_Type_Abstract {

	const INDEX_NAME = '';

	protected $_source = false; // Don't store json-source

	public function __construct($host = null, $port = null, $version = null) {
		$this->_indexName = static::INDEX_NAME;
		$this->_typeName = static::INDEX_NAME;

		$servers = CM_Config::get()->CM_Search->servers;
		$server = $servers[array_rand($servers)];
		if (!$host) {
			$host = $server['host'];
		}
		if (!$port) {
			$port = $server['port'];
		}
		$client = new Elastica_Client(array('host' => $host, 'port' => $port));

		if ($version) {
			$this->_indexName .= '.' . $version;
		}
		if (CM_Bootloader::getInstance()->isEnvironment('test')) {
			$this->_indexName .= '.test';
		}

		parent::__construct($client);
	}

	public function createVersioned() {
		// Remove old unfinished indices
		foreach ($this->_client->getStatus()->getIndicesWithAlias($this->_indexName . '.tmp') as $index) {
			/** @var Elastica_Index $index */
			$index->delete();
		}

		// Set current index to read-only
		foreach ($this->_client->getStatus()->getIndicesWithAlias($this->_indexName) as $index) {
			/** @var Elastica_Index $index */
			$index->getSettings()->setBlocksWrite(true);
		}

		// Create new index and switch alias
		$version = time();
		$indexNew = new static($this->_client->getHost(), $this->_client->getPort(), $version);
		$indexNew->create(true);
		$indexNew->getIndex()->addAlias($this->_indexName . '.tmp');

		$settings = $indexNew->getIndex()->getSettings();
		$refreshInterval = $settings->getRefreshInterval();
		//$mergeFactor = $settings->getMergePolicy('merge_factor');

		//$settings->setMergePolicy('merge_factor', 50);
		$settings->setRefreshInterval('-1');

		$indexNew->update();

		//$settings->setMergePolicy('merge_factor', $mergeFactor);
		$settings->setRefreshInterval($refreshInterval);

		$indexNew->getIndex()->addAlias($this->_indexName);
		$indexNew->getIndex()->removeAlias($this->_indexName . '.tmp');

		// Remove old index
		foreach ($this->_client->getStatus()->getIndicesWithAlias($this->_indexName) as $index) {
			/** @var Elastica_Index $index */
			if ($index->getName() != $indexNew->getIndex()->getName()) {
				$index->delete();
			}
		}
	}

	/**
	 * Update the complete index
	 *
	 * @param mixed[] $ids               Only update given IDs
	 * @param int     $limit             Limit query
	 * @param int     $maxDocsPerRequest Number of docs per bulk-request
	 */
	public function update($ids = null, $limit = null, $maxDocsPerRequest = self::MAX_DOCS_PER_REQUEST) {
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
		$result = CM_Db_Db::exec($query);

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
			$this->getIndex()->getClient()->deleteIds($idsDelete, $this->_indexName, $this->_typeName);
		}
	}

	/**
	 * @param array $data
	 * @return Elastica_Document Document with data
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
	 * @param mixed $entity
	 */
	public static function updateItem($entity) {
		self::_updateItem(self::getIdForItem($entity));
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
	 * @param string $id
	 */
	protected static function _updateItem($id) {
		CM_Cache_Redis::sAdd('Search.Updates_' . static::INDEX_NAME, self::_getIdSerialized($id));
	}
}
