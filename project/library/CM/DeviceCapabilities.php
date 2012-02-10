<?php

class CM_DeviceCapabilities extends CM_Model_Abstract {

	/**
	 * @param string $userAgent
	 */
	public function __construct($userAgent) {
		$userAgent = (string) $userAgent;
		$this->_setCacheLocal();
		$this->_construct(array('id' => (string) $userAgent));
	}

	/**
	 * @return string
	 */
	public function getUserAgent() {
		return $this->getId();
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
		$adapterClass = self::_getConfig()->adapter;
		/** @var CM_DeviceCapabilitiesAdapter_Abstract $adapter */
		$adapter = new $adapterClass($this->getUserAgent());
		$capabilities = $adapter->getCapabilities();
		if (is_null($capabilities)) {
			$capabilities = array('mobile' => false, 'tablet' => false, 'hasTouchscreen' => false);
		}
		return $capabilities;
	}

}
