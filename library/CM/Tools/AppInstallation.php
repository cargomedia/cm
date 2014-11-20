<?php

class CM_Tools_AppInstallation {

    /** @var string */
    protected $_dirRoot;

    /** @var CM_App_Installation */
    protected $_appInstallation;

    /** @var CM_File_Filesystem|null */
    protected $_filesystem;

    /**
     * @param string $dirRoot
     */
    public function __construct($dirRoot) {
        $this->_dirRoot = (string) $dirRoot;
        $this->reload();
    }

    /**
     * @return string
     */
    public function getDirRoot() {
        return $this->_dirRoot;
    }

    /**
     * @return CM_File_Filesystem
     */
    public function getFilesystem() {
        if (null === $this->_filesystem) {
            $filesystemAdapter = new CM_File_Filesystem_Adapter_Local($this->getDirRoot());
            $this->_filesystem = new CM_File_Filesystem($filesystemAdapter);
        }
        return $this->_filesystem;
    }

    /**
     * @return string[]
     */
    public function getModuleNames() {
        return array_keys($this->_getModulePaths());
    }

    /**
     * @param string $name
     * @return string
     * @throws CM_Exception_Invalid
     */
    public function getModulePath($name) {
        if (!$this->moduleExists($name)) {
            throw new CM_Exception_Invalid('Cannot find `' . $name . '` module/namespace within `' . implode('`', $this->getModuleNames()) . '`');
        }
        return $this->_getModulePaths()[$name];
    }

    /**
     * @param string $name
     * @return bool
     */
    public function moduleExists($name) {
        return in_array($name, $this->getModuleNames());
    }

    /**
     * @return bool
     */
    public function isSingleModuleStructure() {
        return count($this->getModulesPathsAvailable()) === 1 && $this->getModulesDirectoryPath() === '';
    }

    /**
     * @return CM_App_Module[]
     */
    public function getRootModules() {
        return $this->_getRootPackage()->getModules();
    }

    /**
     * @return string[]
     */
    public function getModulesPathsAvailable() {
        $modulePaths = array();
        foreach ($this->getRootModules() as $module) {
            $modulePaths[] = $module->getPath();
        }
        $modulePaths = array_map('dirname', $modulePaths);
        return array_unique($modulePaths);
    }

    /**
     * @return mixed
     * @throws CM_Exception_Invalid
     */
    public function getModulesDirectoryPath() {
        $modulesPaths = $this->getModulesPathsAvailable();
        if (count($modulesPaths) > 1) {
            throw new CM_Exception_Invalid('Multiple module root paths in project.');
        }
        if (count($modulesPaths) === 0) {
            return 'modules/';
        }
        return reset($modulesPaths);
    }

    /**
     * @return array
     */
    public function getNamespaces() {
        $composer = $this->getComposer();
        $autoloadGenerator = $composer->getAutoloadGenerator();
        $installationManager = $composer->getInstallationManager();
        $mainPackage = $composer->getPackage();
        $namespaces = array();
        foreach ($this->_appInstallation->getComposerPackagesFiltered() as $package) {
            $packageMap = array(array($package, $installationManager->getInstallPath($package)));
            $autoloads = $autoloadGenerator->parseAutoloads($packageMap, $mainPackage);
            $packageNamespaces = array_merge(
                array_keys($autoloads['psr-0']),
                array_keys($autoloads['psr-4'])
            );
            $namespaces = array_merge($namespaces, $packageNamespaces);
        }
        return array_map(function ($namespace) {
            return preg_replace('/_$/', '', $namespace);
        }, $namespaces);
    }

    /**
     * @param string $namespace
     * @return string
     * @throws CM_Exception_Invalid
     */
    public function getNamespacePath($namespace) {
        try {
            return $this->getModulePath($namespace) . 'library/';
        } catch (CM_Exception_Invalid $e) {
            throw new CM_Exception_Invalid('Namespace `' . $namespace . '` not found within cm-based module-namespaces.');
        }
    }

    /**
     * @return \Composer\Composer
     */
    public function getComposer() {
        return $this->_appInstallation->getComposer();
    }

    public function reload() {
        $this->_appInstallation = new CM_App_Installation($this->_dirRoot);
    }

    /**
     * @param string $path
     * @return bool
     */
    public function fileExists($path) {
        $exists = $this->getFilesystem()->exists($path);
        var_dump($path . ' ' . ($exists ? 'exists' : 'does not exist'));
        return $exists;

    }

    /**
     * @return array [namespace => pathRelative]
     */
    protected function _getModulePaths() {
        return $this->_appInstallation->getModulePaths();
    }

    /**
     * @return CM_App_Package
     */
    private function _getRootPackage() {
        $rootPackageName = $this->getComposer()->getPackage()->getName();
        foreach ($this->_appInstallation->getPackages() as $package) {
            if ($package->getName() === $rootPackageName) {
                return $package;
            }
        }
    }
}
