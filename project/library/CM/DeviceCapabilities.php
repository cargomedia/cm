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

	protected function _loadData() {
		$capabilities = $this->_adapter->getCapabilities();
		if (is_null($capabilities)) {
			throw new CM_Exception_Nonexistent('Unable to fetch properties for userAgent `' . $userAgent . '`.');
		}
		return $capabilities;
	}

}
