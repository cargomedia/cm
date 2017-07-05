<?php

abstract class CM_Cache_Storage_Abstract extends CM_Class_Abstract {

    /** @var CM_Cache_Storage_Runtime|null */
    protected $_runtime;

    public function __construct() {
        $this->_runtime = CM_Cache_Storage_Runtime::getInstance();
    }

    /**
     * @param string   $key
     * @param mixed    $value
     * @param int|null $lifeTime
     */
    public final function set($key, $value, $lifeTime = null) {
        if ($runtime = $this->_getRuntime()) {
            $runtime->set($key, $value);
        }
        CM_Service_Manager::getInstance()->getDebug()->incStats(strtolower($this->_getName()) . '-set', $key);
        $this->_set($this->_getKeyArmored($key), $value, $lifeTime);
    }

    /**
     * @param string $key
     * @return mixed|false
     */
    public final function get($key) {
        $runtime = $this->_getRuntime();
        if ($runtime && false !== ($value = $runtime->get($key))) {
            return $value;
        }

        CM_Service_Manager::getInstance()->getDebug()->incStats(strtolower($this->_getName()) . '-get', $key);
        $value = $this->_get($this->_getKeyArmored($key));
        if ($runtime && false !== $value) {
            $runtime->set($key, $value);
        }
        return $value;
    }

    /**
     * @param string $key
     */
    public final function delete($key) {
        if ($runtime = $this->_getRuntime()) {
            $runtime->delete($key);
        }
        $this->_delete($this->_getKeyArmored($key));
    }

    public final function flush() {
        if ($runtime = $this->_getRuntime()) {
            $runtime->flush();
        }
        $this->_flush();
    }

    /**
     * @param mixed $keyPart ...
     * @return string
     */
    public final function key($keyPart) {
        $parts = func_get_args();
        foreach ($parts as &$part) {
            if (!is_scalar($part)) {
                $part = md5(serialize($part));
            }
        }
        return implode('_', $parts);
    }

    /**
     * @return string
     */
    abstract protected function _getName();

    /**
     * @param string   $key
     * @param mixed    $value
     * @param int|null $lifeTime
     * @return boolean
     */
    abstract protected function _set($key, $value, $lifeTime = null);

    /**
     * @param string $key
     * @return mixed Result or false
     */
    abstract protected function _get($key);

    /**
     * @param string $key
     * @return boolean
     */
    abstract protected function _delete($key);

    /**
     * @return boolean
     */
    abstract protected function _flush();

    /**
     * @param string $key
     * @return string
     */
    protected function _getKeyArmored($key) {
        return CM_Bootloader::getInstance()->getDataPrefix() . DIR_ROOT . '_' . $key;
    }

    /**
     * @return CM_Cache_Storage_Runtime
     */
    protected function _getRuntime() {
        return $this->_runtime;
    }
}
