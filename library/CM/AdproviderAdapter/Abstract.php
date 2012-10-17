<?php

abstract class CM_AdproviderAdapter_Abstract extends CM_Class_Abstract {

	/**
	 * @return CM_AdproviderAdapter_Abstract
	 */
	public static function factory() {
		$className = self::_getClassName();
		return new $className();
	}

	/**
	 * @param array $zoneData
	 * @return string
	 */
	abstract public function getHtml($zoneData);
}
