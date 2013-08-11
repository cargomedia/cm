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
		return CM_Db_Db::select($this->_tableName, '*', $id)->fetch();
	}

	public function save(array $id, array $data) {
		CM_Db_Db::update($this->_tableName, $data, $id);
	}

	public function create(array $data) {
		$id = CM_Db_Db::insert($this->_tableName, $data);
		return array('id' => $id);
	}

	public function delete(array $id) {
		CM_Db_Db::delete($this->_tableName, $id);
	}
}
