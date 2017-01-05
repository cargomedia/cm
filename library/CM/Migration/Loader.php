<?php

class CM_Migration_Loader implements CM_Service_ManagerAwareInterface {

    use CM_Service_ManagerAwareTrait;

    /**  @var CM_File[] */
    private $_files;

    /**  @var array */
    private $_loadedClasses;

    /**
     * @param CM_Service_Manager $serviceManager
     * @param string[]           $paths
     */
    public function __construct(CM_Service_Manager $serviceManager, array $paths) {
        $this->setServiceManager($serviceManager);
        $this->_files = $this->_prepareFiles($paths);
        $this->_loadedClasses = [];
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
     * @throws CM_Exception_Invalid
     */
    protected function _prepareScript(CM_File $file) {
        $serviceManager = $this->getServiceManager();
        $className = $this->_requireScript($file->getPathOnLocalFilesystem());

        /** @var CM_Migration_UpgradableInterface $script */
        $script = new $className();
        if ($script instanceof CM_Service_ManagerAwareInterface) {
            $script->setServiceManager($serviceManager);
        }
        return new CM_Migration_Script($script, $serviceManager);
    }

    /**
     * @param string $filePath
     * @return string
     * @throws CM_Exception_Invalid
     */
    protected function _requireScript($filePath) {
        if (!isset($this->_loadedClasses[$filePath])) {
            $classesBefore = get_declared_classes();
            require_once($filePath);
            $classesAfter = get_declared_classes();
            $classesDiff = array_values(array_diff($classesAfter, $classesBefore));
            $classesUpgradable = \Functional\filter($classesDiff, function ($className) {
                return in_array(CM_Migration_UpgradableInterface::class, class_implements($className));
            });
            if (count($classesUpgradable) !== 1) {
                throw new CM_Exception_Invalid('Migration script must declare one and only one class implementing CM_Migration_UpgradableInterface', null, [
                    'declaredClasses' => $classesDiff,
                    'filePath'        => $filePath,
                ]);
            }
            $this->_loadedClasses[$filePath] = \Functional\first($classesUpgradable);
        }
        return $this->_loadedClasses[$filePath];
    }
}
