<?php

trait CM_Class_ConfigTrait {

    private static $_classConfigCacheEnabled = null;
    private static $_classConfigList = [];

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
    private static function _getConfigRaw() {
        $config = CM_Config::get();
        $result = array();
        foreach (CM_Util::getClassHierarchy(get_called_class()) as $class) {
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
     * @return string
     */
    protected static function _getClassName() {
        $config = self::_getConfig();
        if (empty($config->class)) {
            return get_called_class();
        }
        return $config->class;
    }
}
