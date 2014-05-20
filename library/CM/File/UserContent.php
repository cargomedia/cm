<?php

class CM_File_UserContent extends CM_File {

    const BUCKETS_COUNT = 10000;

    /** @var string */
    private $_pathRelative;

    /** @var string */
    private $_namespace;

    /**
     * @param string   $namespace
     * @param string   $filename
     * @param int|null $sequence
     */
    public function __construct($namespace, $filename, $sequence = null) {
        $namespace = (string) $namespace;
        $filename = (string) $filename;
        if (null !== $sequence) {
            $sequence = (int) $sequence;
        }

        $this->_pathRelative = $this->_calculateRelativeDir($namespace, $filename, $sequence);
        $this->_namespace = $namespace;

        $filesystem = self::getFilesystemByNamespace($this->getNamespace());
        parent::__construct($this->getPathRelative(), $filesystem);
    }

    /**
     * @return string
     */
    public function getPathRelative() {
        return $this->_pathRelative;
    }

    /**
     * @return string
     */
    public function getNamespace() {
        return $this->_namespace;
    }

    /**
     * @return string
     */
    public function getUrl() {
        return self::getUrlByNamespace($this->getNamespace()) . '/' . $this->getPathRelative();
    }

    /**
     * @param string   $namespace
     * @param string   $filename
     * @param int|null $sequence
     * @return string
     */
    private function _calculateRelativeDir($namespace, $filename, $sequence = null) {
        $dirs = array();
        $dirs[] = $namespace;
        if (null !== $sequence) {
            $dirs[] = $sequence % self::BUCKETS_COUNT;
        }
        $dirs[] = $filename;
        return implode('/', $dirs);
    }

    /**
     * @param string $namespace
     * @return CM_File_Filesystem
     */
    public static function getFilesystemByNamespace($namespace) {
        $serviceManager = CM_Service_Manager::getInstance();
        $filesystemKey = self::_getNamespaceConfig($namespace)['filesystem'];
        return $serviceManager->get($filesystemKey, 'CM_File_Filesystem');
    }

    /**
     * @param string $namespace
     * @return string
     */
    public static function getUrlByNamespace($namespace) {
        return (string) self::_getNamespaceConfig($namespace)['url'];
    }

    /**
     * @return CM_File_Filesystem[]
     */
    public static function getFilesystemList() {
        $serviceManager = CM_Service_Manager::getInstance();
        $filesystemList = array();
        foreach (self::_getNamespaceConfigList() as $namespace => $config) {
            $filesystemList[$namespace] = $serviceManager->get($config['filesystem'], 'CM_File_Filesystem');
        }
        return $filesystemList;
    }

    /**
     * @return string[]
     */
    public static function getUrlList() {
        $urlList = array();
        foreach (self::_getNamespaceConfigList() as $namespace => $config) {
            $urlList[$namespace] = (string) $config['url'];
        }
        return $urlList;
    }

    /**
     * @return array
     */
    private static function _getNamespaceConfigList() {
        return self::_getConfig()->namespaces;
    }

    /**
     * @param string $namespace
     * @return array
     */
    private static function _getNamespaceConfig($namespace) {
        $configList = self::_getNamespaceConfigList();
        if (isset($configList[$namespace])) {
            return $configList[$namespace];
        }
        return $configList['default'];
    }
}
