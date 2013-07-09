<?php

abstract class CM_Class_Abstract {

	private static $_classConfigCacheEnabled = null;
	private static $_classHierarchyCache = array();

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
	 * @throws CM_Class_Exception_TypeNotConfiguredException
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
			throw new CM_Class_Exception_TypeNotConfiguredException('Type `' . $type . '` not configured for class `' . get_called_class() . '`.');
		}
		return $config->types[$type];
	}

	/**
	 * @return stdClass
	 * @throws CM_Exception_Invalid
	 */
	protected static function _getConfig() {
		if (null === self::$_classConfigCacheEnabled) {
			self::$_classConfigCacheEnabled = CM_Config::get()->classConfigCacheEnabled;
		}
		$cacheKey = CM_CacheConst::Config . '_className:' . get_called_class();
		if (!self::$_classConfigCacheEnabled || false === ($result = CM_CacheLocal::get($cacheKey))) {
			$result = self::_getConfigRaw();
			CM_CacheLocal::set($cacheKey, $result);
		}
		return $result;
	}

	/**
	 * @return stdClass
	 * @throws CM_Exception_Invalid
	 */
	protected static function _getConfigRaw() {
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
		$className = get_called_class();
		if (isset(self::$_classHierarchyCache[$className])) {
			return self::$_classHierarchyCache[$className];
		}
		$classHierarchy = array_values(class_parents($className));
		array_unshift($classHierarchy, $className);
		array_pop($classHierarchy);
		self::$_classHierarchyCache[$className] = $classHierarchy;
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
