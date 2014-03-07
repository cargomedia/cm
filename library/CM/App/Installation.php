<?php

class CM_App_Installation {

    /** @var \Composer\Composer */
    private $_composer;

    /**
     * @param \Composer\Composer|null $composer
     */
    public function __construct(\Composer\Composer $composer = null) {
        if (null !== $composer) {
            $this->_composer = $composer;
        }
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

        $packages = array();
        /** @var \Composer\Package\CompletePackage[] $composerPackagesFiltered */
        foreach ($composerPackagesFiltered as $package) {
            $packages[] = $this->_getPackageFromComposerPackage($package);
        }
        return $packages;
    }

    /**
     * @return integer
     */
    public function getUpdateStamp() {
        $composerJsonStamp = CM_File::getModified(DIR_ROOT . 'composer.json');
        $installedJsonPath = DIR_ROOT . $this->_getComposerVendorDir() . 'composer/installed.json';
        $installedJsonStamp = CM_File::getModified($installedJsonPath);
        return max($composerJsonStamp, $installedJsonStamp);
    }

    /**
     * @return \Composer\Package\CompletePackage[]
     */
    protected function _getComposerPackages() {
        $repo = $this->_getComposer()->getRepositoryManager()->getLocalRepository();
        $packages = $repo->getPackages();
        $packages[] = $this->_getComposer()->getPackage();
        return $packages;
    }

    /**
     * @return string
     */
    protected function _getComposerVendorDir() {
        $composerJsonStamp = CM_File::getModified(DIR_ROOT . 'composer.json');
        $cacheKey = CM_CacheConst::ComposerVendorDir;
        $fileCache = new CM_Cache_Storage_File();
        if (false === ($vendorDir = $fileCache->get($cacheKey)) || $composerJsonStamp > $fileCache->getCreateStamp($cacheKey)) {
            $vendorDir = rtrim($this->_getComposer()->getConfig()->get('vendor-dir'), '/') . '/';
            $fileCache->set($cacheKey, $vendorDir);
        }
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
        if (!array_key_exists('cm-modules', $extra)) {
            throw new CM_Exception_Invalid('Missing `cm-modules` in `' . $package->getName() . '` package composer extra');
        }
        return new CM_App_Package($package->getName(), $pathRelative, $extra['cm-modules']);
    }

    /**
     * @return \Composer\Composer
     */
    private function _getComposer() {
        if (null === $this->_composer) {
            $this->_composer = self::composerFactory();
        }
        return $this->_composer;
    }

    /**
     * @return \Composer\Composer
     */
    public static function composerFactory() {
        $io = new \Composer\IO\NullIO();

        $composerPath = DIR_ROOT . 'composer.json';
        $composerFile = new Composer\Json\JsonFile($composerPath);
        $composerFile->validateSchema(Composer\Json\JsonFile::LAX_SCHEMA);
        $localConfig = $composerFile->read();

        // Configuration defaults
        $config = new Composer\Config();
        $config->merge(array('config' => array('home' => CM_Bootloader::getInstance()->getDirTmp() . 'composer/')));
        $config->merge($localConfig);

        $vendorDir = DIR_ROOT . $config->get('vendor-dir');

        // initialize repository manager
        $rm = new Composer\Repository\RepositoryManager($io, $config);
        $rm->setRepositoryClass('composer', 'Composer\Repository\ComposerRepository');
        $rm->setRepositoryClass('vcs', 'Composer\Repository\VcsRepository');
        $rm->setRepositoryClass('package', 'Composer\Repository\PackageRepository');
        $rm->setRepositoryClass('pear', 'Composer\Repository\PearRepository');
        $rm->setRepositoryClass('git', 'Composer\Repository\VcsRepository');
        $rm->setRepositoryClass('svn', 'Composer\Repository\VcsRepository');
        $rm->setRepositoryClass('hg', 'Composer\Repository\VcsRepository');
        $rm->setRepositoryClass('artifact', 'Composer\Repository\ArtifactRepository');

        // load local repository
        $rm->setLocalRepository(new Composer\Repository\InstalledFilesystemRepository(new Composer\Json\JsonFile($vendorDir .
            '/composer/installed.json')));

        // load package
        $loader = new Composer\Package\Loader\RootPackageLoader($rm, $config);
        $package = $loader->load($localConfig);

        // initialize composer
        $composer = new Composer\Composer();
        $composer->setConfig($config);
        $composer->setPackage($package);
        $composer->setRepositoryManager($rm);

        return $composer;
    }
}
