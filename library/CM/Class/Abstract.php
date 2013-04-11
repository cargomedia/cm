<?php

abstract class CM_Class_Abstract {

	/** @var string[] */
	private static $_dynamicClassChildren = array();

	/** @var string[] */
	private static $_dynamicClassChildrenAbstract = array();

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

	public static function registerDynamicClass() {
		$className = get_called_class();
		$reflectionClass = new ReflectionClass($className);
		if ($reflectionClass->isAbstract()) {
			$dynamicClassChildren = & self::$_dynamicClassChildrenAbstract;
		} else {
			$dynamicClassChildren = & self::$_dynamicClassChildren;
		}
		$classHierarchy = self::_getClassHierarchy();
		array_shift($classHierarchy);
		foreach ($classHierarchy as $classNameParent) {
			if (!isset($dynamicClassChildren[$classNameParent])) {
				$dynamicClassChildren[$classNameParent] = array();
			}
			if (!in_array($className, $dynamicClassChildren[$classNameParent], true)) {
				array_push($dynamicClassChildren[$classNameParent], $className);
			}
		}
	}

	public static function unregisterDynamicClasses() {
		self::$_dynamicClassChildren = array();
		self::$_dynamicClassChildrenAbstract = array();
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
			throw new CM_Exception_Invalid('Type `' . $type . '` not configured for class `' . get_called_class() . '`.');
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
		$dynamicClassChildren = self::_getDynamicClassChildren($includeAbstracts);
		return array_merge(CM_Util::getClassChildren($className, $includeAbstracts), $dynamicClassChildren);
	}

	/**
	 * @param bool $includeAbstracts
	 * @return string[]
	 */
	private static function _getDynamicClassChildren($includeAbstracts = null) {
		$className = get_called_class();
		$dynamicClassChildren = array();
		if (isset(self::$_dynamicClassChildren[$className])) {
			$dynamicClassChildren = self::$_dynamicClassChildren[$className];
		}
		if ($includeAbstracts && isset(self::$_dynamicClassChildrenAbstract[$className])) {
			$dynamicClassChildren = array_merge($dynamicClassChildren, self::$_dynamicClassChildrenAbstract[$className]);
		}
		return $dynamicClassChildren;
	}
}
