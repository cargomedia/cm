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
            $this->_extendConfigNodeWithFile($node, 'internal.php');
            $this->_extendConfigNodeWithFile($node, 'default.php');
            $this->_extendConfigNodeWithFile($node, 'local.php');
            $this->_extendConfigNodeWithFile($node, 'deploy.php');
            if (CM_Bootloader::getInstance()->isCli()) {
                $this->_extendConfigNodeWithFile($node, 'cli.php');
            }
            if (CM_Bootloader::getInstance() instanceof CM_Bootloader_Testing) {
                $this->_extendConfigNodeWithFile($node, 'test.php');
            }
            $config = $node->export();
            $cache->set($cacheKey, $config);
        }
        $this->_config = $config;
    }

    /**
     * @param CM_Config_Node $config
     * @param string         $filenameRelative
     */
    private function _extendConfigNodeWithFile(CM_Config_Node $config, $filenameRelative) {
        foreach (CM_Util::getResourceFiles('config/' . $filenameRelative) as $file) {
            require $file->getPath();
        }
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
