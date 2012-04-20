<?php

require_once DIR_SMARTY . 'Smarty.class.php';

class CM_Render extends CM_Class_Abstract {

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
	 * @param CM_Site_Abstract|null $site
	 */
	public function __construct(CM_Site_Abstract $site = null) {
		if (!$site) {
			$site = $this->_site = CM_Site_Abstract::factory();
		}
		$this->_site = $site;
	}

	/**
	 * @return CM_Render
	 */
	public static function getInstance() {
		if (!self::$_instance) {
			self::$_instance = new self();
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
	 * @param string $key
	 * @return array Stack
	 */
	public function getStack($key) {
		if (empty($this->_stack[$key])) {
			return array();
		}
		return $this->_stack[$key];
	}

	/**
	 * @param string $key
	 * @return mixed|null
	 */
	public function getStackLast($key) {
		$stack = $this->getStack($key);
		if (empty($stack)) {
			return null;
		}
		return $stack[count($stack) - 1];
	}

	/**
	 * @param string $key
	 * @return mixed|null
	 */
	public function popStack($key) {
		$last = array_pop($this->_stack[$key]);
		return $last;
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
	 * @param CM_View_Abstract $view Object to render
	 * @param array			$params
	 * @return string Output
	 * @throws CM_Exception
	 */
	public function render(CM_View_Abstract $view, array $params = array()) {
		if (!preg_match('/^[a-zA-Z]+_([a-zA-Z]+)(_\w+)?$/', get_class($view), $matches)) {
			throw new CM_Exception("Cannot detect namespace from object's class-name `" . get_class($view) . "`");
		}
		$renderClass = 'CM_RenderAdapter_' . $matches[1];

		/** @var CM_RenderAdapter_Abstract $renderAdapter */
		$renderAdapter = new $renderClass($this, $view);

		return $renderAdapter->fetch($params);
	}

	/**
	 * @param string	 $tplPath
	 * @param array|null $variables
	 * @param bool|null  $isolated
	 * @return string
	 */
	public function renderTemplate($tplPath, $variables = null, $isolated = null) {
		$compileId = $this->getSite()->getId();
		/** @var Smarty_Internal_TemplateBase $template */
		if ($isolated) {
			$template = $this->_getSmarty()->createTemplate($tplPath, null, $compileId);
		} else {
			$template = $this->_getSmarty();
		}
		$template->assignGlobal('render', $this);
		if ($variables) {
			$template->assign($variables);
		}
		if ($isolated) {
			return $template->fetch();
		} else {
			return $template->fetch($tplPath, null, $compileId);
		}
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
				$phrase = 'components.' . get_class($this->getStackLast('components')) . '.' . $phrase;
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
	 * @param string	  $tpl  Template file name
	 * @param string|null $namespace
	 * @param bool|null   $full
	 * @param bool|null   $needed
	 * @return string Layout path based on theme
	 * @throws CM_Exception_Invalid
	 */
	public function getLayoutPath($tpl, $namespace = null, $full = null, $needed = true) {
		if (is_null($full)) {
			$full = false;
		}
		foreach ($this->getSite()->getThemes() as $theme) {
			$file = $this->getThemeDir(true, $theme, $namespace) . $tpl;

			if (file_exists($file)) {
				if ($full) {
					return $file;
				} else {
					return $this->getThemeDir(false, $theme, $namespace) . $tpl;
				}
			}
		}

		if ($needed) {
			throw new CM_Exception_Invalid('Cannot find `' . $tpl . '` in namespace `' . $this->getSite()->getNamespace() . '` and themes `' .
					implode(', ', $this->getSite()->getThemes()) . '`');
		}
		return null;
	}

	/**
	 * @param string		$path
	 * @param string|null   $namespace
	 * @return CM_File
	 * @throws CM_Exception_Invalid
	 */
	public function getLayoutFile($path, $namespace = null) {
		return new CM_File($this->getLayoutPath($path, $namespace, true));
	}

	/**
	 * @param string|null  $path
	 * @param boolean|null $cdn
	 * @return string
	 */
	public function getUrl($path = null, $cdn = null) {
		if (is_null($cdn)) {
			$cdn = false;
		}
		$path = (string) $path;
		$urlBase = $cdn ? $this->getSite()->getUrlCdn() : $this->getSite()->getUrl();
		return $urlBase . $path;
	}

	/**
	 * @param              $pageClassName
	 * @param array|null   $params
	 * @param boolean|null $absolute
	 * @return string
	 */
	public function getUrlPage($pageClassName, array $params = null, $absolute = null) {
		$pageClassName = (string) $pageClassName;
		if (is_null($absolute)) {
			$absolute = false;
		}
		if (!class_exists($pageClassName) || !is_subclass_of($pageClassName, 'CM_Page_Abstract')) {
			throw new CM_Exception_Invalid('Cannot find valid class definition for component `' . $pageClassName . '`.');
		}
		$pathTokens = explode('_', $pageClassName);
		array_shift($pathTokens);
		array_shift($pathTokens);
		// Rewrites CodeOfHonor to code-of-honor
		foreach ($pathTokens as &$pathToken) {
			$pathToken = preg_replace('/([A-Z])/e', '"-".strtolower("$1")', lcfirst($pathToken));
		}
		$path = implode('/', $pathTokens);
		$urlBase = $absolute ? $this->getSite()->getUrl() : '/';
		return $urlBase . CM_Page_Abstract::link($path, $params);
	}

	/**
	 * @param string|null $type
	 * @param string|null $path
	 * @return string
	 */
	public function getUrlResource($type = null, $path = null) {
		$urlPath = '';
		if (!(is_null($type) || is_null($path))) {
			$type = (string) $type;
			$path = (string) $path;
			$urlPath .= $type . '/' . $this->getSite()->getId() . '/' . CM_App::getInstance()->getReleaseStamp() . '/' . $path;
		}
		return $this->getUrl($urlPath, self::_getConfig()->cdnResource);
	}

	/**
	 * @param string $path
	 * @return string
	 */
	public function getUrlStatic($path = null) {
		$urlPath = 'static/';
		if (!is_null($path)) {
			$path = (string) $path;
			$urlPath .= $path . '?' . CM_App::getInstance()->getReleaseStamp();
		}
		return $this->getUrl($urlPath, self::_getConfig()->cdnResource);
	}

	/**
	 * @param CM_File_UserContent $file
	 * @return string
	 */
	public function getUrlUserContent(CM_File_UserContent $file) {
		return $this->getUrl('userfiles/' . $file->getPathRelative(), self::_getConfig()->cdnUserContent);
	}

	/**
	 * @return Smarty
	 */
	private function _getSmarty() {
		if (!isset(self::$_smarty)) {
			self::$_smarty = new Smarty();

			self::$_smarty->setTemplateDir(DIR_LAYOUT);
			self::$_smarty->setCompileDir(DIR_TMP_SMARTY);
			self::$_smarty->_file_perms = 0777;
			self::$_smarty->_dir_perms = 0777;
			self::$_smarty->compile_check = IS_DEBUG;
			self::$_smarty->caching = false;
			self::$_smarty->error_reporting = E_ALL & ~E_NOTICE & ~E_USER_NOTICE;
			foreach ($this->getSite()->getNamespaces() as $namespace) {
				self::$_smarty->addPluginsDir(DIR_LIBRARY . $namespace . '/SmartyPlugins');
			}
		}

		return self::$_smarty;
	}
}
