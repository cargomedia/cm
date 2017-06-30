<?php

class CM_Config {

    /**
     * @var stdClass
     */
    private $_config = null;

    private function _init() {
        $cache = new CM_Cache_Storage_Apc();
        $cacheKey = CM_CacheConst::Config;
        if (false === ($config = $cache->get($cacheKey))) {
            $node = new CM_Config_Node();
            $internalConfigFile = new CM_File(DIR_ROOT . 'resources/config/internal.php');
            if ($internalConfigFile->exists()) {
                $node->extendWithFile($internalConfigFile);
            }
            $node->extend('default.php');
            $node->extend('local.php');
            $node->extend('local.*.php');
            $node->extend('deploy.php');
            if (CM_Bootloader::getInstance()->isCli()) {
                $node->extend('cli.php');
            }
            if (CM_Bootloader::getInstance() instanceof CM_Bootloader_Testing) {
                $node->extend('test.php');
                $node->extend('test.*.php');
            }
            $config = $node->export();
            $cache->set($cacheKey, $config);
        }
        $this->_config = $config;
    }

    /**
     * @return stdClass
     */
    public static function get() {
        $config = self::_getInstance();
        if (!$config->_config) {
            $config->_init();
        }
        return $config->_config;
    }

    /**
     * @param stdClass $config
     */
    public static function set(stdClass $config) {
        self::_getInstance()->_config = $config;
    }

    /**
     * @return CM_Config
     */
    private static function _getInstance() {
        return CM_Bootloader::getInstance()->getConfig();
    }
}
