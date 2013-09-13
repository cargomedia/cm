<?php

class CM_App_Installation {

	/** @var \Composer\Composer */
	private $_composer;

	/**
	 * @param \Composer\Composer|null $composer
	 */
	public function __construct(\Composer\Composer $composer = null) {
		if (null === $composer) {
			$composer = self::composerFactory();
		}
		$this->_composer = $composer;
	}

	/**
	 * @return array [namespace => pathRelative]
	 */
	public function getNamespacePaths() {
		$namespacePaths = array();
		foreach ($this->getPackages() as $package) {
			foreach ($package->getModules() as $module) {
				$namespacePaths[$module->getName()] = $this->_getPackagePath($package) . $module->getPath();
			}
		}
		return $namespacePaths;
	}

	/**
	 * @return CM_App_Package[]
	 * @throws CM_Exception_Invalid
	 */
	public function getPackages() {
		$packageName = 'cargomedia/cm';
		$packages = $this->_getComposerPackages();
		foreach ($packages as $package) {
			if ($package->getName() === $packageName) {
				$fsPackageMain = $package;
			}
		}
		if (!isset($fsPackageMain)) {
			throw new CM_Exception_Invalid('`' . $packageName . '` package not found within composer packages');
		}

		/** @var CM_App_Package[] $fsPackages */
		$fsPackages = array(new CM_App_Package($fsPackageMain));
		for (; $parentPackage = current($fsPackages); next($fsPackages)) {
			foreach ($packages as $package) {
				if (array_key_exists($parentPackage->getName(), $package->getRequires())) {
					$fsPackages[] = new CM_App_Package($package);
				}
			}
		}
		return $fsPackages;
	}

	/**
	 * @return \Composer\Package\CompletePackage[]
	 */
	protected function _getComposerPackages() {
		$repo = $this->_composer->getRepositoryManager()->getLocalRepository();

		$packages = $repo->getPackages();
		$packages[] = $this->_composer->getPackage();
		return $packages;
	}

	/**
	 * @return string
	 */
	protected function _getComposerVendorDir() {
		return rtrim($this->_composer->getConfig()->get('vendor-dir'), '/') . '/';
	}

	/**
	 * @param CM_App_Package $package
	 * @return string
	 */
	protected function _getPackagePath(CM_App_Package $package) {
		$pathRelative = '';
		if (!$package->isRoot()) {
			$vendorDir = $this->_getComposerVendorDir();
			$pathRelative = $vendorDir . $package->getPrettyName() . '/';
		}
		return $pathRelative;
	}

	/**
	 * @return \Composer\Composer
	 */
	public static function composerFactory() {
		if (!getenv('COMPOSER_HOME') && !getenv('HOME')) {
			putenv('COMPOSER_HOME=' . sys_get_temp_dir() . 'composer/');
		}
		$oldCwd = getcwd();
		chdir(DIR_ROOT);
		$io = new \Composer\IO\NullIO();
		$composer = \Composer\Factory::create($io, DIR_ROOT . 'composer.json');
		chdir($oldCwd);
		return $composer;
	}
}
