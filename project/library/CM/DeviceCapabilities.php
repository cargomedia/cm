<?php

class CM_DeviceCapabilities extends CM_Model_Abstract {

	/**
	 * @var string $_userAgent
	 */
	private $_userAgent;

	/**
	 * @param string $userAgent
	 */
	public function __construct($userAgent) {
		$userAgent = (string) $userAgent;
		$this->_setCacheLocal();
		$this->_userAgent = $userAgent;
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
		$adapterClass = self::_getConfig()->adapter;
		$adapter = new $adapterClass($this->_userAgent);
		$capabilities = $adapter->getCapabilities();
		if (is_null($capabilities)) {
			$capabilities = array('mobile' => false, 'tablet' => false, 'hasTouchscreen' => false);
		}
		return $capabilities;
	}

}
