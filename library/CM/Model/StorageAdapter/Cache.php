<?php

class CM_Model_StorageAdapter_Cache extends CM_Model_StorageAdapter_AbstractAdapter {

  public function load($type, array $id) {
    return CM_Cache_Shared::getInstance()->get($this->_getCacheKey($type, $id));
  }

  public function loadMultiple(array $idTypeList) {
    $cacheEntryToArrayKey = array();
    foreach ($idTypeList as $key => $idType) {
      $cacheKey = $this->_getCacheKey($idType['type'], $idType['id']);
      $cacheEntryToArrayKey[$cacheKey] = $key;
    }
    $result = array();
    $values = CM_Cache_Shared::getInstance()->getMulti(array_keys($cacheEntryToArrayKey));
    foreach ($values as $cacheKey => $value) {
      $key = $cacheEntryToArrayKey[$cacheKey];
      $result[$key] = $value;
    }
    return $result;
  }

  public function save($type, array $id, array $data) {
    CM_Cache_Shared::getInstance()->set($this->_getCacheKey($type, $id), $data);
  }

  public function create($type, array $data) {
    $this->save($type, $this->_generateId(), $data);
  }

  public function delete($type, array $id) {
    CM_Cache_Shared::getInstance()->delete($this->_getCacheKey($type, $id));
  }

  /**
   * @param int   $type
   * @param array $id
   * @return string
   */
  protected function _getCacheKey($type, array $id) {
    return CM_CacheConst::CM_Model_StorageAdapter_Cache . '_type:' . $type . '_id:' . serialize($id);
  }

  /**
   * @return array
   */
  protected function _generateId() {
    throw new CM_Exception_NotImplemented();
  }
}
