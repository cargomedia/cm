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
		$config = CM_Config::get();
		foreach (self::_getClassHierarchy() as $class) {
			if (isset($config->$class)) {
				return $config->$class;
			}
		}
		throw new CM_Exception_Invalid('Class `' . get_called_class() . '` has no configuration.');
	}

	/**
	 * @param string|null $className
	 * @throws CM_Exception_Invalid
	 * @return string
	 */
	protected static function _getNamespace($className = null) {
		if (!$className) {
			$className = get_called_class();
		}
		$position = strpos($className, '_');
		if (false === $position || 0 === $position) {
			throw new CM_Exception_Invalid('Could not detect namespace of `' . $className . '`.');
		}
		$namespace = substr($className, 0, $position);
		return $namespace;
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
		$key = CM_CacheConst::ClassChildren . '_className:' . $className . '_abstracts:' . (int) $includeAbstracts;
		if (false === ($classNames = CM_CacheLocal::get($key))) {
			$pathsFiltered = array();
			$paths = CM_Util::rglob('*.php', DIR_LIBRARY);
			sort($paths);
			foreach ($paths as $path) {
				$file = new CM_File($path);
				$regexp = '#class\s+(?<name>.+?)\b#';
				if (preg_match($regexp, $file->read(), $matches)) {
					if (class_exists($matches['name'], true)) {
						$reflectionClass = new ReflectionClass($matches['name']);
						if ($reflectionClass->isSubclassOf($className) && (!$reflectionClass->isAbstract() || $includeAbstracts)) {
							$pathsFiltered[] = $path;
						}
					}
				}
			}
			$classNames = CM_Util::getClasses($pathsFiltered);
			CM_CacheLocal::set($key, $classNames);
		}
		return $classNames;
	}
}
