<?php

class CM_Options implements CM_Service_ManagerAwareInterface {

    use CM_Service_ManagerAwareTrait;

    /**
     * @param string $key
     * @return mixed|null
     * @throws CM_Exception_Invalid
     */
    public function get($key) {
        $cacheKey = CM_CacheConst::Option;
        $cache = CM_Cache_Shared::getInstance();
        if (($options = $cache->get($cacheKey)) === false) {
            $query = new CM_Db_Query_Select($this->_getDatabaseClient(), 'cm_option', array('key', 'value'));
            $options = $query->execute()->fetchAllTree();
            $cache->set($cacheKey, $options);
        }
        if (!isset($options[$key])) {
            return null;
        }
        $value = unserialize($options[$key]);
        if (false === $value) {
            throw new CM_Exception_Invalid('Cannot unserialize option.', null, ['key' => $key]);
        }
        return $value;
    }

    /**
     * @param string $key
     * @param mixed  $value
     */
    public function set($key, $value) {
        $fields = array('key' => $key, 'value' => serialize($value));
        $query = new CM_Db_Query_Insert($this->_getDatabaseClient(), 'cm_option', $fields, null, null, 'REPLACE');
        $query->execute();
        $this->_clearCache();
    }

    /**
     * @param string $key
     */
    public function delete($key) {
        $query = new CM_Db_Query_Delete($this->_getDatabaseClient(), 'cm_option', array('key' => $key));
        $query->execute();
        $this->_clearCache();
    }

    /**
     * @param string   $key
     * @param int|null $change
     * @return int New value
     */
    public function inc($key, $change = null) {
        if (is_null($change)) {
            $change = +1;
        }
        $value = (int) $this->get($key);
        $value += (int) $change;
        $this->set($key, $value);
        return $value;
    }

    /**
     * @return CM_Db_Client
     * @throws CM_Exception_Invalid
     */
    protected function _getDatabaseClient() {
        return $this->getServiceManager()->getDatabases()->getMaster();
    }

    private function _clearCache() {
        $cacheKey = CM_CacheConst::Option;
        CM_Cache_Shared::getInstance()->delete($cacheKey);
    }
}
