<?php

class CM_Model_StorageAdapter_Database extends CM_Model_StorageAdapter_AbstractAdapter {

	public function load($type, array $id) {
		return CM_Db_Db::select($this->_getTableName($type), '*', $id)->fetch();
	}

	public function save($type, array $id, array $data) {
		CM_Db_Db::update($this->_getTableName($type), $data, $id);
	}

	public function create($type, array $data) {
		$id = CM_Db_Db::insert($this->_getTableName($type), $data);
		return array('id' => $id);
	}

	public function delete($type, array $id) {
		CM_Db_Db::delete($this->_getTableName($type), $id);
	}

	/**
	 * @param int $type
	 * @return string
	 */
	protected function _getTableName($type) {
		$className = CM_Model_Abstract::getClassName($type);
		$className = strtolower($className);
		return $className;
	}
}
