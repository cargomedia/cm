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
        $this->_files = $this->_findFilesWithinDirectories($paths);
        $this->_loadedClasses = [];
    }

    /**
     * @param string $name
     * @return CM_Migration_Runner|null
     */
    public function findRunner($name) {
        $file = \Functional\first($this->_getFiles(), function (CM_File $file) use ($name) {
            return $name === $file->getFileNameWithoutExtension();
        });
        return null !== $file ? $this->_instantiateRunner($file) : null;
    }

    /**
     * @return Generator|CM_Migration_Runner[]
     */
    public function getRunnerList() {
        foreach ($this->_getFiles() as $file) {
            yield $this->_instantiateRunner($file);
        }
    }

    /**
     * @return CM_File[]
     */
    protected function _getFiles() {
        return $this->_files;
    }

    /**
     * @param string[] $paths
     * @return CM_File[]
     */
    protected function _findFilesWithinDirectories($paths) {
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
     * @return CM_Migration_Runner
     * @throws CM_Exception_Invalid
     */
    protected function _instantiateRunner(CM_File $file) {
        $serviceManager = $this->getServiceManager();
        $className = $this->_requireScript($file);

        /** @var CM_Migration_UpgradableInterface $script */
        $script = new $className();
        if ($script instanceof CM_Service_ManagerAwareInterface) {
            $script->setServiceManager($serviceManager);
        }
        return new CM_Migration_Runner($script, $serviceManager);
    }

    /**
     * @param CM_File $file
     * @return string
     * @throws CM_Exception_Invalid
     */
    protected function _requireScript(CM_File $file) {
        $filePath = $file->getPathOnLocalFilesystem();
        if (!isset($this->_loadedClasses[$filePath])) {
            $classes = $this->_getClassNameList($file->read());
            if (count($classes) !== 1) {
                throw new CM_Exception_Invalid('Migration script must declare one class and one class only', null, [
                    'classes'  => $classes,
                    'filePath' => $filePath,
                ]);
            }
            require_once($filePath);
            $className = \Functional\first($classes);
            if (!in_array(CM_Migration_UpgradableInterface::class, class_implements($className))) {
                throw new CM_Exception_Invalid('Migration script must implements CM_Migration_UpgradableInterface', null, [
                    'classes'  => $classes,
                    'filePath' => $filePath,
                ]);
            }
            $this->_loadedClasses[$filePath] = $className;
        }
        return $this->_loadedClasses[$filePath];
    }

    /**
     * @param string $phpCode
     * @return string[]
     */
    protected function _getClassNameList($phpCode) {
        $classes = array();
        $tokens = token_get_all($phpCode);
        $count = count($tokens);
        for ($i = 2; $i < $count; $i++) {
            if ($tokens[$i - 2][0] === T_CLASS
                && $tokens[$i - 1][0] === T_WHITESPACE
                && $tokens[$i][0] === T_STRING
            ) {
                $classes[] = $tokens[$i][1];
            }
        }
        return $classes;
    }
}
