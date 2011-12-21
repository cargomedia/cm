<?php

abstract class CM_Class_Abstract {

	/**
	 * @return int
	 */
	public function getType() {
		return static::TYPE;
	}

	/**
	 * @param int $type
	 * @return string
	 * @throws CM_Exception_Invalid
	 */
	protected static function _getClassName($type = null) {
		$type = (int) $type;
		$config = self::_getConfig();
		if (!$type) {
			if (empty($config->class)) {
				return get_called_class();
			}
			return $config->class;
		}
		if (empty($config->types[$type])) {
			throw new CM_Exception_Invalid('Type `' . $type . '` not configured for class `' . get_called_class() . '`.');
		}
		return $config->types[$type];
	}

	/**
	 * @return stdClass
	 * @throws CM_Exception_Invalid
	 */
	protected static function _getConfig() {
		$className = get_called_class();
		$config = Config::get()->$className;
		if (empty($config)) {
			throw new CM_Exception_Invalid('Class `' . get_called_class() . '` has no configuration.');
		}
		return $config;
	}
}
