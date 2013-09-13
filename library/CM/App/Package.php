<?php

class CM_App_Package {

	/** @var string */
	private $_path;

	/** @var string */
	private $_name;

	/** @var CM_App_Module[] */
	private $_modules;

	/**
	 * @param string     $name
	 * @param string     $path
	 * @param array|null $modules
	 * @throws CM_Exception_Invalid
	 */
	public function __construct($name, $path, array $modules = null) {
		$this->_name = (string) $name;
		$this->_path = (string) $path;

		$this->_modules = array();
		if (null !== $modules) {
			foreach ($modules as $moduleName => $moduleParams) {
				$path = $moduleParams['path'];
				$this->addModule($moduleName, $path);
			}
		}
	}

	/**
	 * @param string $name
	 * @param string $path
	 */
	public function addModule($name, $path) {
		$this->_modules[] = new CM_App_Module($name, $this->getPath() . $path);
	}

	/**
	 * @return CM_App_Module[]
	 */
	public function getModules() {
		return $this->_modules;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->_name;
	}

	/**
	 * @return string
	 */
	public function getPath() {
		return $this->_path;
	}
}
