<?php

class CM_App_ComposerFactory extends CM_Class_Abstract {

	/**
	 * @param array $localConfig
	 * @return \Composer\Composer
	 */
	public function createComposer(array $localConfig) {
		$io = new \Composer\IO\NullIO();
		$composer = new \Composer\Composer();

		$composerConfig = new \Composer\Config();
		$composerConfig->merge(array('config' => array('home' => CM_Bootloader::getInstance()->getDirTmp() . 'composer/')));
		$composerConfig->merge($localConfig);
		$composer->setConfig($composerConfig);

		$im = $this->createInstallationManager();
		$composer->setInstallationManager($im);

		$this->createDefaultInstallers($im, $composer, $io);

		$dispatcher = new \Composer\Script\EventDispatcher($composer, $io);
		$composer->setEventDispatcher($dispatcher);

		$generator = new \Composer\Autoload\AutoloadGenerator($dispatcher);
		$composer->setAutoloadGenerator($generator);

		$rm = $this->createRepositoryManager($composer, $io);
		$composer->setRepositoryManager($rm);

		$loader = new \Composer\Package\Loader\RootPackageLoader($rm, $composerConfig);
		$package = $loader->load($localConfig);
		$composer->setPackage($package);

		return $composer;
	}

	/**
	 * @param Composer\Composer       $composer
	 * @param Composer\IO\IOInterface $io
	 * @return \Composer\Repository\RepositoryManager
	 */
	public function createRepositoryManager(\Composer\Composer $composer, Composer\IO\IOInterface $io) {
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

	/**
	 * @param \Composer\Installer\InstallationManager $im
	 * @param \Composer\Composer                      $composer
	 * @param \Composer\IO\IOInterface                $io
	 */
	public function createDefaultInstallers(Composer\Installer\InstallationManager $im, Composer\Composer $composer, \Composer\IO\IOInterface $io) {
		$im->addInstaller(new \Composer\Installer\LibraryInstaller($io, $composer, null));
		$im->addInstaller(new \Composer\Installer\PearInstaller($io, $composer, 'pear-library'));
//		$im->addInstaller(new \Composer\Installer\InstallerInstaller($io, $composer));
		$im->addInstaller(new \Composer\Installer\MetapackageInstaller($io));
	}

	/**
	 * @return \Composer\Installer\InstallationManager
	 */
	public function createInstallationManager() {
		return new \Composer\Installer\InstallationManager();
	}
}
