<?php

class CM_Service_UserContent extends CM_Service_ManagerAware {

    /** @var array */
    private $_config;

    /**
     * @param array $config
     */
    public function __construct(array $config) {
        $this->_config = $config;
    }

    /**
     * @param string $namespace
     * @return CM_File_Filesystem
     */
    public function getFilesystem($namespace) {
        $filesystemKey = $this->_getNamespaceConfig($namespace)['filesystem'];
        return $this->getServiceManager()->get($filesystemKey, 'CM_File_Filesystem');
    }

    /**
     * @param string $namespace
     * @return string
     */
    public function getUrl($namespace) {
        return (string) $this->_getNamespaceConfig($namespace)['url'];
    }

    /**
     * @return CM_File_Filesystem[]
     */
    public function getFilesystemList() {
        $filesystemList = array();
        foreach ($this->_config as $namespace => $config) {
            $filesystemList[$namespace] = $this->getServiceManager()->get($config['filesystem'], 'CM_File_Filesystem');
        }
        return $filesystemList;
    }

    /**
     * @return string[]
     */
    public function getUrlList() {
        $urlList = array();
        foreach ($this->_config as $namespace => $config) {
            $urlList[$namespace] = (string) $config['url'];
        }
        return $urlList;
    }

    /**
     * @param string $namespace
     * @return array
     */
    private function _getNamespaceConfig($namespace) {
        if (isset($this->_config[$namespace])) {
            return $this->_config[$namespace];
        }
        return $this->_config['default'];
    }
}
