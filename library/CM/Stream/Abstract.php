<?php

abstract class CM_Stream_Abstract extends CM_Class_Abstract {

    /** @var CM_Stream_Adapter_Abstract */
    protected $_adapter;

    /**
     * @throws CM_Exception_NotImplemented
     * @return CM_Stream_Abstract
     */
    public static function getInstance() {
        throw new CM_Exception_NotImplemented();
    }

    /**
     * @return bool
     */
    public function getEnabled() {
        return (bool) $this->_enabled;
    }

    /**
     * @return CM_Stream_Adapter_Abstract
     */
    public function getAdapter() {
        return $this->_adapter;
    }
}
