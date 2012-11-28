<?php

class CM_Search extends CM_Class_Abstract {

	/** @var Elastica_Client */
	private $_client;

	/** @var CM_Search */
	private static $_instance;

	public function __construct() {
		$this->_client = new Elastica_Client(array('servers' => self::_getConfig()->servers, 'timeout' => 10));
	}

	/**
	 * @return bool
	 */
	public function getEnabled() {
		return (bool) self::_getConfig()->enabled;
	}

	/**
	 * @param string $indexName
	 * @param string $typeName
	 * @param array  $data
	 * @return array
	 */
	public function query($indexName, $typeName, array $data = null) {
		if (!$this->getEnabled()) {
			return array();
		}
		CM_Debug::get()->incStats('search', json_encode($data));

		$type = new Elastica_Type(new Elastica_Index($this->_client, $indexName), $typeName);
		$search = new Elastica_Search($this->_client);
		$search->addIndex($type->getIndex());
		$search->addType($type);
		$response = $this->_client->request($search->getPath(), 'GET', $data);
		return $response->getData();
	}

	/**
	 * @return CM_Search
	 */
	public static function getInstance() {
		if (!self::$_instance) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
}
