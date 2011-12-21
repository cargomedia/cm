<?php

require_once DIR_SMARTY . 'Smarty.class.php';

class CM_Render {

	/**
	 * @var CM_Render
	 */
	private static $_instance = null;

	/**
	 * @var Smarty
	 */
	private static $_smarty = null;

	/**
	 * @var CM_Frontend
	 */
	protected $_js = null;

	/**
	 * @var CM_Site_Abstract
	 */
	protected $_site = null;

	public static $block_cap = '';

	/**
	 * Currently opened blocks stack.
	 *
	 * @var array
	 */
	public static $block_stack = array();

	/**
	 * Stack for rendering processes
	 *
	 * @var array
	 */
	protected $_stack = array();

	/**
	 * @param CM_RequestHandler_Abstract|null $requestHandler
	 */
	public function __construct(CM_RequestHandler_Abstract $requestHandler = null) {
		if ($requestHandler) {
			$this->_requestHandler = $requestHandler;
			$this->_site = $requestHandler->getSite();
		} else {
			$this->_site = new SK_Site_FB();
		}

		// Language global vars
		$officialInfoConfigs = CM_Config::section('site')->Section('official')->getConfigsList();
		$globals = array();

		foreach ($officialInfoConfigs as $item) {
			$globals[$item->name] = $item->value;
		}
		CM_Language::defineGlobal($globals);
	}

	/**
	 * @param CM_RequestHandler_Abstract|null $requestHandler
	 * @return CM_Render
	 */
	public static function getInstance(CM_RequestHandler_Abstract $requestHandler = null) {
		if (!self::$_instance) {
			self::$_instance = new self($requestHandler);
		}
		return self::$_instance;
	}

	/**
	 * @return CM_Site_Abstract
	 */
	public function getSite() {
		return $this->_site;
	}

	/**
	 * @return CM_Frontend
	 */
	public function getJs() {
		if (!$this->_js) {
			$this->_js = new CM_Frontend();
		}
		return $this->_js;
	}

	/**
	 * @return Smarty
	 */
	public function getLayout() {
		if (!isset(self::$_smarty)) {
			self::$_smarty = new Smarty();

			self::$_smarty->setTemplateDir(DIR_LAYOUT);
			self::$_smarty->setCompileDir(DIR_TMP_SMARTY);
			self::$_smarty->_file_perms = 0777;
			self::$_smarty->_dir_perms = 0777;
			umask(0);
			self::$_smarty->compile_check = DEBUG_MODE;
			self::$_smarty->caching = false;
			self::$_smarty->error_reporting = E_ALL & ~E_NOTICE & ~E_USER_NOTICE;
			foreach ($this->getSite()->getNamespaces() as $namespace) {
				self::$_smarty->addPluginsDir(DIR_LIBRARY . $namespace . '/SmartyPlugins');
			}

			SK_Tracking::getInstance()->setPageview();
		}

		return self::$_smarty;
	}

	/**
	 * @return CM_RequestHandler_Abstract
	 */
	public function getRequestHandler() {
		if (!$this->_requestHandler) {
			// @todo: Has or not has requestHandler?
			throw new CM_Exception_Invalid('Render has no requestHandler');
		}
		return $this->_requestHandler;
	}

	/**
	 * @param string $key
	 * @return array Stack
	 */
	public function getStack($key) {
		if (empty($this->_stack[$key])) {
			return array();
		}
		return $this->_stack[$key];
	}

	public function getStackLast($key) {
		return $this->_stack[$key][count($this->_stack[$key]) - 1];
	}

	public function popStack($key) {
		array_pop($this->_stack[$key]);
		return $this->getStack($key);
	}

	/**
	 * @param string $key
	 * @param mixed  $value
	 * @return array Stack values
	 */
	public function pushStack($key, $value) {
		if (empty($this->_stack[$key])) {
			$this->_stack[$key] = array();
		}

		array_push($this->_stack[$key], $value);
		return $this->getStack($key);
	}

	/**
	 * @param CM_Renderable_Abstract $object Object to render
	 * @param array				  $params
	 * @return string Output
	 * @throws CM_Exception
	 */
	public function render(CM_Renderable_Abstract $object, array $params = array()) {
		if (!preg_match('/^[a-zA-Z]+_([a-zA-Z]+)_\w+$/', get_class($object), $matches)) {
			throw new CM_Exception("Cannot detect namespace from object's class-name `" . get_class($object) . "`");
		}

		$renderClass = 'CM_RenderAdapter_' . $matches[1];

		/** @var CM_RenderAdapter_Abstract $renderObject */
		$renderObject = new $renderClass($this, $object);
		$this->getLayout()->assignGlobal('render', $this);

		return $renderObject->fetch($params);
	}

	/**
	 * @param string $phrase
	 * @param array  $params OPTIONAL
	 * @return string
	 */
	public function getText($phrase, $params = array()) {
		if ($phrase[0] == '%') {
			$phrase = substr($phrase, 1);
			if ($phrase[0] == '.') {
				$phrase = substr($phrase, 1);
			} else {
				$phrase = 'components.' . $this->getStackLast('components')->getNamespaceLegacy() . '.' . $phrase;
			}
			$text = CM_Language::text($phrase, $params);
		} else {
			$text = CM_Language::exec($phrase, $params);
		}
		return $text;
	}

	/**
	 * @param bool   $full	  OPTIONAL True if full path required
	 * @param string $theme	 OPTIONAL
	 * @param string $namespace OPTIONAL
	 * @return string Theme base path
	 */
	public function getThemeDir($full = false, $theme = null, $namespace = null) {
		if (!$theme) {
			$theme = $this->getSite()->getTheme();
		}

		if (!$namespace) {
			$namespace = $this->getSite()->getNamespace();
		}

		$path = $namespace . DIRECTORY_SEPARATOR . $theme . DIRECTORY_SEPARATOR;

		if ($full) {
			$path = DIR_LAYOUT . $path;
		}
		return $path;
	}

	/**
	 * @param string $tpl  Template file name
	 * @param bool   $full OPTIONAL True for full path
	 * @return string Layout path based on theme
	 * @throws CM_Exception
	 */
	public function getLayoutPath($tpl, $full = false, $namespace = null) {

		foreach ($this->getSite()->getThemes() as $theme) {
			$file = $this->getThemeDir(true, $theme, $namespace) . $tpl;

			if (file_exists($file)) {
				if ($full) {
					return $file;
				} else {
					return $file = $this->getThemeDir(false, $theme, $namespace) . $tpl;
				}
			}
		}

		throw new CM_Exception_Invalid('Cannot find `' . $tpl . '` in namespace `' . $this->getSite()->getNamespace() . '` and themes `' .
				implode(', ', $this->getSite()->getThemes()) . '`');
	}

	/**
	 * @param string $path
	 * @return string
	 */
	public function getUrlImg($path) {
		return URL_OBJECTS . 'img/' . $this->getSite()->getId() . '/' . Config::get()->modified . '/' . $path;
	}

	/**
	 * @param string $path
	 * @return CM_File
	 */
	public function getFileThemed($path) {
		return new CM_File($this->getLayoutPath($path, true));
	}
}
