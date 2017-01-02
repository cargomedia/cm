<?php

class CM_Migration_Loader implements CM_Service_ManagerAwareInterface {

    use CM_Service_ManagerAwareTrait;

    /**  @var CM_File[] */
    private $_files;

    /**
     * @param CM_Service_Manager $serviceManager
     * @param string[]           $paths
     */
    public function __construct(CM_Service_Manager $serviceManager, array $paths) {
        $this->setServiceManager($serviceManager);
        $this->_files = $this->_prepareFiles($paths);
    }

    /**
     * @param string $name
     * @return CM_Migration_Script|null
     */
    public function findScript($name) {
        $file = \Functional\first($this->_getFiles(), function (CM_File $file) use ($name) {
            return $name === $file->getFileNameWithoutExtension();
        });
        return null !== $file ? $this->_prepareScript($file) : null;
    }

    /**
     * return Iterator
     */
    public function getScriptList() {
        foreach ($this->_getFiles() as $file) {
            yield $this->_prepareScript($file);
        }
    }

    /**
     * @return CM_File[]
     */
    protected function _getFiles() {
        return $this->_files;
    }

    /**
     * @return CM_File[]
     */
    protected function _prepareFiles($paths) {
        $files = [];
        foreach ($paths as $path) {
            foreach (CM_Util::rglob('*.php', $path) as $filePath) {
                $files[] = new CM_File($filePath);
            }
        }
        return $files;
    }

    /**
     * @param CM_File $file
     * @return CM_Migration_Script
     */
    protected function _prepareScript(CM_File $file) {
        require_once($file->getPath());
        $className = sprintf('CM_Migration_Script_%s', $file->getFileNameWithoutExtension());
        return new $className($this->getServiceManager());
    }
}
