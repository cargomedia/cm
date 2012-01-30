<?php

abstract class CM_DeviceCapabilitiesAdapter_Abstract extends CM_Model_Abstract {

	/**
	 * @returns boolean
	 */
	abstract public function isMobile();

	/**
	 * @param string|null $userAgent
	 * @return CM_DeviceCapabilitiesAdapter_Abstract
	 */
	public static function factory($userAgent = null) {
		$classname = self::_getClassName();
		return new $classname($userAgent);
	}

}
