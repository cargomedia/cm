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
		$composerJsonStamp = CM_File::getModified($this->_dirRoot . 'composer.json');
		$installedJsonPath = $this->_dirRoot . $this->_getComposerVendorDir() . 'composer/installed.json';
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
		$composerJsonStamp = CM_File::getModified($this->_dirRoot . 'composer.json');
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
		$modules = array();
		if (array_key_exists('cm-modules', $extra)) {
			$modules = $extra['cm-modules'];
		}
		return new CM_App_Package($package->getName(), $pathRelative, $modules);
	}

	/**
	 * @return \Composer\Composer
	 */
	protected function _getComposer() {
		if (null === $this->_composer) {
			$this->_composer = $this->_createComposerDefault();
		}
		return $this->_composer;
	}

	/**
	 * @return \Composer\Composer
	 */
	private function _createComposerDefault() {
		$composerPath = $this->_dirRoot . 'composer.json';
		$composerFile = new Composer\Json\JsonFile($composerPath);
		$composerFile->validateSchema(Composer\Json\JsonFile::LAX_SCHEMA);
		$localConfig = $composerFile->read();

		$composerFactory = new CM_App_ComposerFactory();
		$composer = $composerFactory->createComposer($localConfig);

		$vendorDir = $this->_dirRoot . $composer->getConfig()->get('vendor-dir');
		$vendorConfig = new Composer\Json\JsonFile($vendorDir . '/composer/installed.json');
		$vendorRepository = new Composer\Repository\InstalledFilesystemRepository($vendorConfig);
		$composer->getRepositoryManager()->setLocalRepository($vendorRepository);
		return $composer;
	}
}
