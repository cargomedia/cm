<?php

require_once DIR_SMARTY . 'Smarty.class.php';

class CM_Render extends CM_Class_Abstract {

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

	/**
	 * @var CM_Model_Language|null
	 */
	private $_language;

	/**
	 * @var IntlDateFormatter
	 */
	private $_dateFormatter;

	/**
	 * @var bool
	 */
	private $_languageRewrite;

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
	 * @param CM_Site_Abstract|null          $site
	 * @param CM_Model_Language|null         $language
	 * @param boolean|null                   $languageRewrite
	 */
	public function __construct(CM_Site_Abstract $site = null, CM_Model_Language $language = null, $languageRewrite = null) {
		if (!$site) {
			$site = CM_Site_Abstract::factory();
		}
		if (!$language) {
			$language = CM_Model_Language::findDefault();
		}
		$this->_site = $site;
		$this->_language = $language;
		$this->_languageRewrite = (bool) $languageRewrite;
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
	 * @param array            $params
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
	 * @param string     $tplPath
	 * @param array|null $variables
	 * @param bool|null  $isolated
	 * @return string
	 */
	public function renderTemplate($tplPath, $variables = null, $isolated = null) {
		$compileId = $this->getSite()->getId();
		if ($this->getLanguage()) {
			$compileId .= '_' . $this->getLanguage()->getAbbreviation();
		}
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
	 * @param bool   $full      OPTIONAL True if full path required
	 * @param string $theme     OPTIONAL
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
	 * @param string      $tpl  Template file name
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
	 * @param string        $path
	 * @param string|null   $namespace
	 * @return CM_File
	 * @throws CM_Exception_Invalid
	 */
	public function getLayoutFile($path, $namespace = null) {
		return new CM_File($this->getLayoutPath($path, $namespace, true));
	}

	/**
	 * @param string|null                  $path
	 * @param boolean|null                 $cdn
	 * @param CM_Site_Abstract|null        $site
	 * @return string
	 */
	public function getUrl($path = null, $cdn = null, CM_Site_Abstract $site = null) {
		if (null === $path) {
			$path = '';
		}
		if (null === $cdn) {
			$cdn = false;
		}
		if (null === $site) {
			$site = $this->getSite();
		}
		$path = (string) $path;
		$urlBase = $cdn ? $site->getUrlCdn() : $site->getUrl();
		return $urlBase . $path;
	}

	/**
	 * @param CM_Page_Abstract|string      $pageClassName
	 * @param array|null                   $params
	 * @param CM_Site_Abstract|null        $site
	 * @param CM_Model_Language|null       $language
	 * @throws CM_Exception_Invalid
	 * @return string
	 */
	public function getUrlPage($pageClassName, array $params = null, CM_Site_Abstract $site = null, CM_Model_Language $language = null) {
		if (null === $site) {
			$site = $this->getSite();
		}
		if ($pageClassName instanceof CM_Page_Abstract) {
			$pageClassName = get_class($pageClassName);
		}
		$pageClassName = (string) $pageClassName;

		if (!class_exists($pageClassName) || !is_subclass_of($pageClassName, 'CM_Page_Abstract')) {
			throw new CM_Exception_Invalid('Cannot find valid class definition for page `' . $pageClassName . '`.');
		}
		if (!preg_match('/^([A-Za-z]+)_/', $pageClassName, $matches)) {
			throw new CM_Exception_Invalid('Cannot find namespace of `' . $pageClassName . '`');
		}
		$namespace = $matches[1];
		if (!in_array($namespace, $site->getNamespaces())) {
			throw new CM_Exception_Invalid('Site `' . get_class($site) . '` does not contain namespace `' . $namespace . '`');
		}
		$path = $pageClassName::getPath($params);

		$languageRewrite = $this->_languageRewrite || $language;
		if (!$language) {
			$language = $this->getLanguage();
		}
		if ($languageRewrite && $language) {
			$path = '/' . $language->getAbbreviation() . $path;
		}
		return $this->getUrl($path, false, $site);
	}

	/**
	 * @param string|null $type
	 * @param string|null $path
	 * @return string
	 */
	public function getUrlResource($type = null, $path = null) {
		$urlPath = '';
		if (!is_null($type) && !is_null($path)) {
			$type = (string) $type;
			$path = (string) $path;
			$urlPath .= '/' . $type;
			if ($this->getLanguage()) {
				$urlPath .= '/' . $this->getLanguage()->getAbbreviation();
			}
			$urlPath .= '/' . $this->getSite()->getId() . '/' . CM_App::getInstance()->getReleaseStamp() . '/' . $path;
		}
		return $this->getUrl($urlPath, self::_getConfig()->cdnResource);
	}

	/**
	 * @param CM_Mail $mail
	 * @return string
	 * @throws CM_Exception_Invalid
	 */
	public function getUrlEmailTracking(CM_Mail $mail) {
		if (!$mail->getRecipient()) {
			throw new CM_Exception_Invalid('Needs user');
		}
		$params = array('user' => $mail->getRecipient()->getId(), 'mailType' => $mail->getType());
		return CM_Util::link($this->getSite()->getUrl() . '/emailtracking/' . $this->getSite()->getId(), $params);
	}

	/**
	 * @param string|null $path
	 * @return string
	 */
	public function getUrlStatic($path = null) {
		$urlPath = '/static';
		if (null !== $path) {
			$urlPath .= $path . '?' . CM_App::getInstance()->getReleaseStamp();
		}
		return $this->getUrl($urlPath, self::_getConfig()->cdnResource);
	}

	/**
	 * @param CM_File_UserContent|null $file
	 * @return string
	 */
	public function getUrlUserContent(CM_File_UserContent $file = null) {
		if (is_null($file)) {
			return $this->getUrl('/userfiles', self::_getConfig()->cdnUserContent);
		}
		return $this->getUrl('/userfiles/' . $file->getPathRelative(), self::_getConfig()->cdnUserContent);
	}

	/**
	 * @return CM_Model_Language|null
	 */
	public function getLanguage() {
		return $this->_language;
	}

	/**
	 * @param string      $key
	 * @param array|null  $params
	 * @return string
	 */
	public function getTranslation($key, array $params = null) {
		$params = (array) $params;
		$translation = $key;
		if ($this->getLanguage()) {
			$translation = $this->getLanguage()->getTranslation($key, array_keys($params));
		}
		$translation = $this->_parseVariables($translation, $params);
		return $translation;
	}

	public function clearTemplates() {
		$this->_getSmarty()->clearCompiledTemplate();
	}

	/**
	 * @return IntlDateFormatter
	 */
	public function getDateFormatter() {
		if (!$this->_dateFormatter) {
			$locale = 'en';
			if ($this->getLanguage()) {
				$locale = $this->getLanguage()->getAbbreviation();
			}
			$this->_dateFormatter = new IntlDateFormatter($locale, IntlDateFormatter::FULL, IntlDateFormatter::FULL);
		}
		return $this->_dateFormatter;
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
		}

		$pluginDirs = array(DIR_SMARTY . 'plugins');
		foreach ($this->getSite()->getNamespaces() as $namespace) {
			$pluginDirs[] = DIR_LIBRARY . $namespace . '/SmartyPlugins';
		}
		self::$_smarty->setPluginsDir($pluginDirs);
		self::$_smarty->loadFilter('pre', 'translate');

		return self::$_smarty;
	}

	/**
	 * @param              $phrase
	 * @param array        $variables
	 * @return string
	 */
	private function _parseVariables($phrase, array $variables) {
		return preg_replace('~\{\$(\w+)\}~ie', "isset(\$variables['\\1']) ? \$variables['\\1'] : ''", $phrase);
	}
}
