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
	 * @param CM_Elastica_Type_Abstract[] $types
	 * @param array|null                $data
	 * @return array
	 */
	public function query(array $types, array $data = null) {
		if (!$this->getEnabled()) {
			return array();
		}
		CM_Debug::get()->incStats('search', json_encode($data));

		$search = new Elastica_Search($this->_client);
		foreach ($types as $type) {
			$search->addIndex($type->getIndex());
			$search->addType($type->getType());
		}
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
