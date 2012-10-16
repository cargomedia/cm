<?php

abstract class CM_Tracking_Abstract extends CM_Class_Abstract {

	/**
	 * @return string
	 */
	abstract public function getJs();

	/**
	 * @return string
	 */
	abstract public function getHtml();

	/**
	 * @return boolean
	 */
	public function enabled() {
		return (boolean) self::_getConfig()->enabled;
	}

	/**
	 * @return string
	 */
	public function getCode() {
		return (string) self::_getConfig()->code;
	}

	/**
	 * @return CM_Tracking
	 */
	public static function factory() {
		$className = self::_getClassName();
		return new $className;
	}
}
