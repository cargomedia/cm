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
		$this->_setCacheLocal();
		$adapterClass = self::_getConfig()->adapter;
		$this->_adapter = new $adapterClass($userAgent);
		parent::__construct($userAgent);
	}

	/**
	 * @return boolean
	 */
	public function isMobile() {
		return $this->_get('mobile');
	}

	/**
	 * @return boolean
	 */
	public function isTablet() {
		return $this->_get('tablet');
	}

	/**
	 * @return boolean
	 */
	public function hasTouchscreen() {
		return $this->_get('hasTouchscreen');
	}

	protected function _loadData() {
		$capabilities = $this->_adapter->getCapabilities();
		return $capabilities;
	}

}
