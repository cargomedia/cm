<?php

abstract class CM_Site_Abstract extends CM_Class_Abstract {

	protected $_themes = array();
	protected $_namespaces = array();

	/**
	 * Default constructor to set CM namespace
	 */
	public function __construct() {
		$this->_setNamespace('CM');
	}
	
	/**
	 * @return string
	 */
	public function getNamespace() {
		return $this->_namespaces[0];
	}
	
	/**
	 * @return string[]
	 */
	public function getNamespaces() {
		return $this->_namespaces;
	}
	
	/**
	 * @return string Theme
	 */
	public function getTheme() {
		return $this->_themes[0];
	}
	
	/**
	 * @return string[]
	 */
	public function getThemes() {
		return $this->_themes;
	}

	/**
	 * @param CM_Request_Abstract $request
	 * @return CM_Request_Abstract
	 */
	public function rewrite(CM_Request_Abstract $request) {
		return $request;
	}
	
	/**
	 * @param string $theme
	 * @return CM_Site_Abstract
	 */
	protected function _addTheme($theme) {
		array_unshift($this->_themes, (string) $theme);
		return $this;
	}

	/**
	 * @param string $namespace
	 * @return CM_Site_Abstract
	 */
	protected function _setNamespace($namespace) {
		array_unshift($this->_namespaces, (string) $namespace);
		// Resets themes if new namespace is set
		$this->_themes = array('default');
		return $this;
	}
	
	/**
	* @param int $type|null
	* @return CM_Site_Abstract
	* @throws CM_Exception
	*/
	public static function factory($type = null) {
		// Currently needed as forms etc. do not have a site yet
		if (!$type) {
			$type = 1;
		}
		$class = self::_getClassName($type);
		return new $class();
	}

	/**
	 * @return int Site id
	 */
	public function getId() {
		return static::TYPE;
	}
}
