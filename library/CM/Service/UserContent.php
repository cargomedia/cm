<?php

class CM_Service_UserContent extends CM_Service_ManagerAware {

    /** @var array */
    private $_configList;

    /**
     * @param array $configList
     */
    public function __construct(array $configList) {
        foreach ($configList as $namespace => $config) {
            $this->_addNamespaceConfig($namespace, $config);
        }
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
        return $this->_getNamespaceConfig($namespace)['url'];
    }

    /**
     * @return CM_File_Filesystem[]
     */
    public function getFilesystemList() {
        $filesystemList = array();
        foreach ($this->_configList as $namespace => $config) {
            $filesystemList[$namespace] = $this->getServiceManager()->get($config['filesystem'], 'CM_File_Filesystem');
        }
        return $filesystemList;
    }

    /**
     * @return string[]
     */
    public function getUrlList() {
        $urlList = array();
        foreach ($this->_configList as $namespace => $config) {
            $urlList[$namespace] = $config['url'];
        }
        return $urlList;
    }

    /**
     * @param string $namespace
     * @return array
     */
    private function _getNamespaceConfig($namespace) {
        if (isset($this->_configList[$namespace])) {
            return $this->_configList[$namespace];
        }
        return $this->_configList['default'];
    }

    /**
     * @param string $namespace
     * @param array  $config
     */
    private function _addNamespaceConfig($namespace, array $config) {
        $namespace = (string) $namespace;
        $config = array(
            'url'        => (string) $config['url'],
            'filesystem' => (string) $config['filesystem'],
        );
        $this->_configList[$namespace] = $config;
    }
}
