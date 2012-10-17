<?php

class CM_Adprovider extends CM_Class_Abstract {

	/** @var CM_AdproviderAdapter_Abstract */
	private $_adapter;

	/**
	 * @param string $zone
	 * @return string
	 */
	public function getHtml($zone) {
		if (!$this->_getEnabled()) {
			return '';
		}
		return $this->_getAdapter()->getHtml($zone);
	}

	/**
	 * @return CM_AdproviderAdapter_Abstract
	 */
	private function _getAdapter() {
		if (!$this->_adapter) {
			$this->_adapter = CM_AdproviderAdapter_Abstract::factory();
		}
		return $this->_adapter;
	}

	/**
	 * @return bool
	 */
	private function _getEnabled() {
		return (bool) self::_getConfig()->enabled;
	}
}
