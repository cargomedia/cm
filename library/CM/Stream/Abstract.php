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
		return (bool) self::_getConfig()->enabled;
	}

	/**
	 * @return array
	 */
	public function getOptions() {
		return $this->_getAdapter()->getOptions();
	}

	/**
	 * @return string
	 */
	public function getAdapterClass() {
		return $this->_getConfig()->adapter;
	}

	/**
	 * @return CM_Stream_Adapter_Abstract
	 */
	protected function _getAdapter() {
		if (!$this->_adapter) {
			$className = $this->getAdapterClass();
			$this->_adapter = new $className();
		}
		return $this->_adapter;
	}

}