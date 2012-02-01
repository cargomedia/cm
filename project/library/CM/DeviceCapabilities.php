<?php

class CM_DeviceCapabilities extends CM_Model_Abstract {

	/**
	 * @var CM_DeviceCapabilitiesAdapter_Abstract $_adapter
	 */
	private $_adapter;

	/**
	 * @param string $userAgent
	 */
	public function __construct($userAgent) {
		$adapterClass = self::_getConfig()->adapter;
		$this->_adapter = new $adapterClass($userAgent);
		parent::__construct($userAgent);
	}

	public function isMobile() {
		return $this->_get('mobile');
	}

	public function isTablet() {
		return $this->_get('tablet');
	}

	public function hasTouchscreen() {
		return $this->_get('hasTouchscreen');
	}

	protected function _loadData() {
		$capabilities = $this->_adapter->getCapabilities();
		return $capabilities;
	}

}
