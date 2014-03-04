<?php

class CM_Cache_Storage_Memcache extends CM_Cache_Storage_Abstract {

  /** @var CM_Memcache_Client */
  private $_client;

  public function __construct() {
    $this->_client = new CM_Memcache_Client();
    parent::__construct();
  }

  protected function _getName() {
    return 'Memcache';
  }

  protected function _set($key, $value, $lifeTime = null) {
    $this->_client->set($key, $value, $lifeTime);
  }

  protected function _get($key) {
    $key = (string) $key;
    return $this->_client->get($key);
  }

  protected function _delete($key) {
    $this->_client->delete($key);
  }

  protected function _flush() {
    $this->_client->flush();
  }

  protected function _getMulti(array $keys) {
    return $this->_client->get($keys);
  }
}
