<?php

abstract class CM_Site_Abstract extends CM_Class_Abstract {

	protected $_themes = array();
	protected $_namespaces = array();
	/**
	 * @var CM_EventHandler
	 */
	protected $_eventHandler = null;

	/**
	 * Default constructor to set CM namespace
	 */
	public function __construct() {
		$this->_setNamespace('CM');
	}

	/**
	 * @param CM_Request_Abstract $request
	 * @return boolean
	 */
	public static function match(CM_Request_Abstract $request) {
		return false;
	}

	/**
	 * @return CM_EventHandler
	 */
	public function getEventHandler() {
		if (!$this->_eventHandler) {
			$this->_eventHandler = new CM_EventHandler();
			$this->bindEvents($this->_eventHandler);
		}
		return $this->_eventHandler;
	}

	/**
	 * @param CM_EventHandler $eventHandler
	 */
	public function bindEvents(CM_EventHandler $eventHandler) {
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
	 * @return string
	 */
	public function getUrlCdn() {
		return self::_getConfig()->urlCdn;
	}

	/**
	 * @return string
	 */
	public function getUrl() {
		return self::_getConfig()->url;
	}

	/**
	 * @param CM_Request_Abstract $request
	 */
	public function rewrite(CM_Request_Abstract $request) {
		$pathParams = explode('/', $request->getPath());
		array_shift($pathParams);
		if (count($pathParams) > 0) {
			$languagePaging = new CM_Paging_Language_Enabled();
			if ($language = $languagePaging->findByAbbreviation(array_shift($pathParams))) {
				$request->setLanguageUrl($language);
				$request->setPath('/' . implode('/', $pathParams));
			}
		}

		if ($request->getPath() == '/') {
			$request->setPath('/index');
		}
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
	 * @param int|null $type
	 * @return CM_Site_Abstract
	 * @throws CM_Exception
	 */
	public static function factory($type = null) {
		$class = self::_getClassName($type);
		return new $class();
	}

	/**
	 * @return int Site id
	 */
	public function getId() {
		return static::TYPE;
	}

	/**
	 * @param CM_Request_Abstract $request
	 * @return CM_Site_Abstract
	 * @throws CM_Exception_Invalid
	 */
	public static function findByRequest(CM_Request_Abstract $request) {
		/** @var CM_Site_Abstract $className */
		foreach (self::findAll() as $className) {
			if ($className::match($request)) {
				return new $className();
			}
		}
		throw new CM_Exception_Invalid('Cannot identify site from current request');
	}

	/**
	 * @return string[]
	 */
	public static function findAll() {
		$key = CM_CacheConst::Sites;
		if (false === ($sites = CM_CacheLocal::get($key))) {
			$sites = array();
			foreach (CM_Util::getClassChildren(get_class()) as $className) {
				$reflectionClass = new ReflectionClass($className);
				if (!$reflectionClass->isAbstract()) {
					$sites[] = $className;
				}
			}
			CM_CacheLocal::set($key, $sites);
		}
		return $sites;
	}
}
