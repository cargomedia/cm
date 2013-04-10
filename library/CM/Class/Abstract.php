<?php

abstract class CM_Class_Abstract {

	/**
	 * @return int
	 */
	public function getType() {
		return static::TYPE;
	}

	/**
	 * @return string[] List of class names
	 */
	public function getClassHierarchy() {
		return self::_getClassHierarchy();
	}

	/**
	 * @param int $type
	 * @return string
	 * @throws CM_Exception_Invalid
	 */
	protected static function _getClassName($type = null) {
		$config = self::_getConfig();
		if (null === $type || empty($config->types)) {
			if (empty($config->class)) {
				return get_called_class();
			}
			return $config->class;
		}
		$type = (int) $type;
		if (empty($config->types[$type])) {
			throw new CM_Exception_Invalid('Type `' . $type . '` not configured for class `' . get_called_class() . '`.', null, null, CM_Exception::WARN);
		}
		return $config->types[$type];
	}

	/**
	 * @return stdClass
	 * @throws CM_Exception_Invalid
	 */
	protected static function _getConfig() {
		$config = CM_Config::get();
		$result = array();
		foreach (self::_getClassHierarchy() as $class) {
			if (isset($config->$class)) {
				$result = array_merge((array) $config->$class, $result);
			}
		}
		if (empty($result)) {
			throw new CM_Exception_Invalid('Class `' . get_called_class() . '` has no configuration.');
		}
		return (object) $result;
	}

	/**
	 * @throws CM_Exception_Invalid
	 * @return string
	 */
	protected static function _getClassNamespace() {
		return CM_Util::getNamespace(get_called_class());
	}

	/**
	 * @return string[]
	 */
	private static function _getClassHierarchy() {
		$classHierarchy = array_values(class_parents(get_called_class()));
		array_unshift($classHierarchy, get_called_class());
		array_pop($classHierarchy);
		return $classHierarchy;
	}

	/**
	 * @param boolean|null $includeAbstracts
	 * @return string[]
	 */
	public static function getClassChildren($includeAbstracts = null) {
		$className = get_called_class();
		return CM_Util::getClassChildren($className, $includeAbstracts);
	}
}
