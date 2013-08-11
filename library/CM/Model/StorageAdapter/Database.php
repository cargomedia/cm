<?php

class CM_Model_StorageAdapter_Database extends CM_Model_StorageAdapter_AbstractAdapter {

	/** @var string */
	private $_tableName;

	/**
	 * @param string $tableName
	 */
	public function __construct($tableName) {
		$this->_tableName = (string) $tableName;
	}

	public function load(array $id) {
		$data = CM_Db_Db::select($this->_tableName, '*', $id)->fetch();
		if (false === $data) {
			return null;
		}
		return $data;
	}

	public function save(array $id, array $data) {
		CM_Db_Db::update($this->_tableName, $data, $id);
	}
}
