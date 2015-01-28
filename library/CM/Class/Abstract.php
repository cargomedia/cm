<?php

abstract class CM_Class_Abstract {

    private static $_classConfigCacheEnabled = null;
    private static $_classHierarchyCache = array();
    private static $_classConfigList = [];

    /**
     * @return int
     * @throws CM_Class_Exception_TypeNotConfiguredException
     */
    public function getType() {
        return static::getTypeStatic();
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
        $calledClass = get_called_class();
        $cacheKey = CM_CacheConst::ClassConfig . '_className:' . $calledClass;
        $cache = new CM_Cache_Storage_Apc();
        if (!self::$_classConfigCacheEnabled || !isset(self::$_classConfigList[$calledClass])) {
            if (!self::$_classConfigCacheEnabled || false === ($result = $cache->get($cacheKey))) {
                $result = self::_getConfigRaw();
                if (self::$_classConfigCacheEnabled) {
                    $cache->set($cacheKey, $result);
                }
            }
            self::$_classConfigList[$calledClass] = $result;
        }
        return self::$_classConfigList[$calledClass];
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

    /**
     * @return int
     * @throws CM_Class_Exception_TypeNotConfiguredException
     */
    public static function getTypeStatic() {
        if (!isset(self::_getConfig()->type)) {
            throw new CM_Class_Exception_TypeNotConfiguredException('Class `' . get_called_class() . '` has no type configured.');
        }
        return self::_getConfig()->type;
    }
}
