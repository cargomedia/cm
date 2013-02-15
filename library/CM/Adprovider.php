<?php

class CM_Adprovider extends CM_Class_Abstract {

	/** @var CM_Adprovider|null */
	private static $_instance;

	/** @var CM_AdproviderAdapter_Abstract[] */
	private $_adapters = array();

	/** @var array */
	private $_zones;

	public function __construct() {
		$this->_zones = self::_getConfig()->zones;
	}

	/**
	 * @param string          $zoneName
	 * @param string[]|null   $variables
	 * @throws CM_Exception_Invalid
	 * @return string
	 */
	public function getHtml($zoneName, $variables = null) {
		$zoneName = (string) $zoneName;
		$variables = (array) $variables;
		if (!$this->_getEnabled()) {
			return '';
		}
		if (!array_key_exists($zoneName, $this->_zones)) {
			throw new CM_Exception_Invalid('Zone `' . $zoneName . '` not configured.');
		}
		$zoneData = $this->_zones[$zoneName];
		if (!array_key_exists('adapter', $zoneData)) {
			throw new CM_Exception_Invalid('Zone `' . $zoneName . '` has no adapter configured.');
		}
		$adapterClassName = (string) $zoneData['adapter'];
		unset($zoneData['adapter']);
		return (string) $this->_getAdapter($adapterClassName)->getHtml($zoneData, $variables);
	}

	/**
	 * @param string $className
	 * @return CM_AdproviderAdapter_Abstract
	 * @throws CM_Exception_Invalid
	 */
	private function _getAdapter($className) {
		/** @var string $className */
		$className = (string) $className;
		if (!class_exists($className) || !is_subclass_of($className, 'CM_AdproviderAdapter_Abstract')) {
			throw new CM_Exception_Invalid('Invalid ad adapter `' . $className . '`');
		}
		if (!array_key_exists($className, $this->_adapters)) {
			$this->_adapters[$className] = new $className();
		}
		return $this->_adapters[$className];
	}

	/**
	 * @return bool
	 */
	private function _getEnabled() {
		return (bool) self::_getConfig()->enabled;
	}

	/**
	 * @return CM_Adprovider
	 */
	public static function getInstance() {
		if (!self::$_instance) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
}
