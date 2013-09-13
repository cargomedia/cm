<?php

class CM_Library_Module {

	/** @var string */
	private $_name;

	/** @var string */
	private $_path;

	/** @var CM_Library_Package */
	private $_package;

	/**
	 * @param string                           $name
	 * @param string                           $path
	 * @param CM_Library_Package $package
	 */
	public function __construct($name, $path, CM_Library_Package $package) {
		$this->_name = (string) $name;
		$this->_path = (string) $path;
		$this->_package = $package;
	}

	/**
	 * @return array [$namespace => $path]
	 */
	public function getNamespaces() {
		$namespaces = array();
		foreach ($this->_package->getNamespaces() as $namespace => $path) {
			if ($path === $this->_path . 'library/') {
				$namespaces[$namespace] = $this->_path;
			}
		}
		return $namespaces;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->_name;
	}

}
