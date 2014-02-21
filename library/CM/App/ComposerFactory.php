<?php

class CM_App_ComposerFactory extends CM_Class_Abstract {

	/**
	 * @return \Composer\Composer
	 */
	public static function createComposerLocal() {
		$composerPath = DIR_ROOT . 'composer.json';
		$composerFile = new Composer\Json\JsonFile($composerPath);
		$composerFile->validateSchema(Composer\Json\JsonFile::LAX_SCHEMA);
		$localConfig = $composerFile->read();

		$composerFactory = new CM_App_ComposerFactory();
		$composer = $composerFactory->createComposer($localConfig);

		$vendorDir = DIR_ROOT . $composer->getConfig()->get('vendor-dir');
		$vendorConfig = new Composer\Json\JsonFile($vendorDir . '/composer/installed.json');
		$vendorRepository = new Composer\Repository\InstalledFilesystemRepository($vendorConfig);
		$composer->getRepositoryManager()->setLocalRepository($vendorRepository);
		return $composer;
	}

	/**
	 * @param array $localConfig
	 * @return \Composer\Composer
	 */
	public function createComposer(array $localConfig) {
		$composer = new \Composer\Composer();

		$composerConfig = new \Composer\Config();
		$composerConfig->merge(array('config' => array('home' => CM_Bootloader::getInstance()->getDirTmp() . 'composer/')));
		$composerConfig->merge($localConfig);
		$composer->setConfig($composerConfig);

		$rm = $this->createRepositoryManager($composer);
		$composer->setRepositoryManager($rm);

		$loader = new \Composer\Package\Loader\RootPackageLoader($rm, $composerConfig);
		$package = $loader->load($localConfig);
		$composer->setPackage($package);

		return $composer;
	}

	/**
	 * @param Composer\Composer $composer
	 * @return \Composer\Repository\RepositoryManager
	 */
	public function createRepositoryManager(\Composer\Composer $composer) {
		$io = new \Composer\IO\NullIO();
		$config = $composer->getConfig();
		$rm = new \Composer\Repository\RepositoryManager($io, $config);

		$rm->setRepositoryClass('composer', 'Composer\Repository\ComposerRepository');
		$rm->setRepositoryClass('vcs', 'Composer\Repository\VcsRepository');
		$rm->setRepositoryClass('package', 'Composer\Repository\PackageRepository');
		$rm->setRepositoryClass('pear', 'Composer\Repository\PearRepository');
		$rm->setRepositoryClass('git', 'Composer\Repository\VcsRepository');
		$rm->setRepositoryClass('svn', 'Composer\Repository\VcsRepository');
		$rm->setRepositoryClass('hg', 'Composer\Repository\VcsRepository');
		$rm->setRepositoryClass('artifact', 'Composer\Repository\ArtifactRepository');
		return $rm;
	}
}
