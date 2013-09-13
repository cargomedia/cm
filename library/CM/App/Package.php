<?php

class CM_App_Package {

	/** @var \Composer\Package\CompletePackage */
	private $_composerPackage;

	/**
	 * @param \Composer\Package\CompletePackage $composerPackage
	 */
	public function __construct(\Composer\Package\CompletePackage $composerPackage) {
		$this->_composerPackage = $composerPackage;
	}

	/**
	 * @return CM_App_Module[]
	 * @throws CM_Exception_Invalid
	 */
	public function getModules() {
		$extra = $this->_composerPackage->getExtra();
		if (!array_key_exists('cm-modules', $extra)) {
			throw new CM_Exception_Invalid('No modules specified for package `' . $this->getName() . '`');
		}
		$modules = array();
		foreach ($extra['cm-modules'] as $moduleName => $modulePath) {
			$modules[] = new CM_App_Module($moduleName, $modulePath, $this);
		}
		return array_reverse($modules);
	}

	/**
	 * @return array [$namespace => $path]
	 */
	public function getNamespaces() {
		$autoload = $this->_composerPackage->getAutoload();
		$namespaces = array();
		if (array_key_exists('psr-0', $autoload)) {
			foreach ($autoload['psr-0'] as $namespace => $path) {
				$namespace = rtrim($namespace, '_');
				$namespaces[$namespace] = $path;
			}
		}
		return array_reverse($namespaces);
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->_composerPackage->getName();
	}

	/**
	 * @return string
	 */
	public function getPrettyName() {
		return $this->_composerPackage->getPrettyName();
	}

	/**
	 * @return bool
	 */
	public function isRoot() {
		return $this->_composerPackage instanceof \Composer\Package\RootPackage;
	}

}
