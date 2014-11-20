<?php

class CM_App_Installation {

    /** @var \Composer\Composer */
    private $_composer;

    /** @var string */
    protected $_dirRoot;

    /**
     * @param string                  $dirRoot
     * @param \Composer\Composer|null $composer
     */
    public function __construct($dirRoot, \Composer\Composer $composer = null) {
        $this->_dirRoot = (string) $dirRoot;
        if (null !== $composer) {
            $this->_composer = $composer;
        }
    }

    /**
     * @return string
     */
    public function getDirRoot() {
        return $this->_dirRoot;
    }

    /**
     * @return array [namespace => pathRelative]
     */
    public function getModulePaths() {
        $namespacePaths = array();
        foreach ($this->getModules() as $module) {
            $namespacePaths[$module->getName()] = $module->getPath();
        }
        return $namespacePaths;
    }

    /**
     * @return CM_App_Module[]
     */
    public function getModules() {
        $modules = array();
        foreach ($this->getPackages() as $package) {
            foreach ($package->getModules() as $module) {
                $modules[] = $module;
            }
        }
        return $modules;
    }

    /**
     * @return CM_App_Package[]
     * @throws CM_Exception_Invalid
     */
    public function getPackages() {
        return \Functional\map($this->getComposerPackagesFiltered(), function (\Composer\Package\CompletePackage $package) {
            return $this->_getPackageFromComposerPackage($package);
        });
    }

    /**
     * @return \Composer\Package\CompletePackage[]
     * @throws CM_Exception_Invalid
     */
    public function getComposerPackagesFiltered() {
        $mainPackageName = 'cargomedia/cm';
        $composerPackages = $this->_getComposerPackages();
        foreach ($composerPackages as $package) {
            if ($package->getName() === $mainPackageName) {
                $composerPackagesFiltered = array($package);
            }
        }
        if (!isset($composerPackagesFiltered)) {
            throw new CM_Exception_Invalid('`' . $mainPackageName . '` package not found within composer packages');
        }

        for (; $parentPackage = current($composerPackagesFiltered); next($composerPackagesFiltered)) {
            foreach ($composerPackages as $package) {
                if (array_key_exists($parentPackage->getName(), $package->getRequires())) {
                    $composerPackagesFiltered[] = $package;
                }
            }
        }
        return $composerPackagesFiltered;
    }

    /**
     * @return integer
     */
    public function getUpdateStamp() {
        $fileComposerJson = new CM_File($this->_dirRoot . 'composer.json');
        $fileInstalledJson = new CM_File($this->_dirRoot . $this->_getComposerVendorDir() . 'composer/installed.json');
        return max($fileComposerJson->getModified(), $fileInstalledJson->getModified());
    }

    /**
     * @return \Composer\Package\CompletePackage[]
     */
    protected function _getComposerPackages() {
        $repo = $this->getComposer()->getRepositoryManager()->getLocalRepository();
        $packages = $repo->getPackages();
        $packages[] = $this->getComposer()->getPackage();
        return $packages;
    }

    /**
     * @return string
     */
    protected function _getComposerVendorDir() {
        $fileComposerJson = new CM_File($this->_dirRoot . 'composer.json');
        $cacheKey = CM_CacheConst::ComposerVendorDir;
        $fileCache = new CM_Cache_Storage_File();
        if (false === ($vendorDir = $fileCache->get($cacheKey)) || $fileComposerJson->getModified() > $fileCache->getCreateStamp($cacheKey)) {
            echo 'vendor-dir uncached';
            $vendorDir = rtrim($this->getComposer()->getConfig()->get('vendor-dir'), '/') . '/';
            $fileCache->set($cacheKey, $vendorDir);
        }
        var_dump('vendor-dir', $vendorDir);
        return $vendorDir;
    }

    /**
     * @param \Composer\Package\CompletePackage $package
     * @return CM_App_Package
     * @throws CM_Exception_Invalid
     */
    protected function _getPackageFromComposerPackage(\Composer\Package\CompletePackage $package) {
        $pathRelative = '';
        if (!$package instanceof \Composer\Package\RootPackage) {
            $vendorDir = $this->_getComposerVendorDir();
            $pathRelative = $vendorDir . $package->getPrettyName() . '/';
        }

        $extra = $package->getExtra();
        $modules = array();
        if (array_key_exists('cm-modules', $extra)) {
            $modules = $extra['cm-modules'];
        }
        return new CM_App_Package($package->getName(), $pathRelative, $modules);
    }

    /**
     * @return \Composer\Composer
     */
    public function getComposer() {
        if (null === $this->_composer) {
            $factory = new CM_App_ComposerFactory();
            $this->_composer = $factory->createComposerFromRootDir($this->_dirRoot);
        }
        return $this->_composer;
    }
}
