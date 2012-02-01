<?php

abstract class CM_DeviceCapabilitiesAdapter_Abstract extends CM_Class_Abstract {

	/**
	 * @var string $_userAgent
	 */
	protected $_useragent;

	/**
	 * @param string $userAgent
	 */
	public function __construct($userAgent) {
		$userAgent = (string) $userAgent;
		$this->_useragent = $userAgent;
	}

	/**
	 * @returns array|null
	 */
	abstract public function getCapabilities();
}
