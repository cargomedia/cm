<?php
require_once 'Util.php';

class CM_Bootloader {

	/** @var CM_Config|null */
	private $_config = null;

	/** @var string[] */
	private $_environments = array();

	/** @var boolean */
	private $_loaded = false;

	/** @var array|null */
	private $_namespacePaths;

	/** @var CM_Bootloader */
	protected static $_instance;

	/** @var CM_ExceptionHandling_Handler_Abstract */
	private $_exceptionHandler;

	/**
	 * @param string      $pathRoot
	 * @param string|null $dirLibrary
	 * @throws CM_Exception_Invalid
	 */
	final public function __construct($pathRoot, $dirLibrary) {
		if (self::$_instance) {
			throw new CM_Exception_Invalid('Bootloader already instantiated');
		}
		self::$_instance = $this;
		define('DIR_ROOT', $pathRoot);
		define('DIR_LIBRARY', $dirLibrary);
	}

	public function defaults() {
		date_default_timezone_set(CM_Config::get()->timeZone);
		mb_internal_encoding('UTF-8');
		umask(0);
		CMService_Newrelic::getInstance()->setConfig();
	}

	public function constants() {
		define('DIR_VENDOR', DIR_ROOT . 'vendor' . DIRECTORY_SEPARATOR);
		define('DIR_PUBLIC', DIR_ROOT . 'public' . DIRECTORY_SEPARATOR);

		define('DIR_DATA', !empty(CM_Config::get()->dirData) ? CM_Config::get()->dirData : DIR_ROOT . 'data' . DIRECTORY_SEPARATOR);
		define('DIR_DATA_LOCKS', DIR_DATA . 'locks' . DIRECTORY_SEPARATOR);
		define('DIR_DATA_LOG', DIR_DATA . 'logs' . DIRECTORY_SEPARATOR);
		define('DIR_DATA_SVM', DIR_DATA . 'svm' . DIRECTORY_SEPARATOR);

		define('DIR_TMP', !empty(CM_Config::get()->dirTmp) ? CM_Config::get()->dirTmp : DIR_ROOT . 'tmp' . DIRECTORY_SEPARATOR);
		define('DIR_TMP_SMARTY', DIR_TMP . 'smarty' . DIRECTORY_SEPARATOR);
		define('DIR_TMP_CACHE', DIR_TMP . 'cache' . DIRECTORY_SEPARATOR);

		define('DIR_USERFILES', !empty(CM_Config::get()->dirUserfiles) ? CM_Config::get()->dirUserfiles :
				DIR_PUBLIC . 'userfiles' . DIRECTORY_SEPARATOR);
	}

	/**
	 * @return CM_Config
	 */
	public function getConfig() {
		if (null === $this->_config) {
			$this->_config = new CM_Config();
		}
		return $this->_config;
	}

	/**
	 * @return string[]
	 */
	public function getNamespaces() {
		return array('CM');
	}

	/**
	 * @return string[]
	 */
	public function getEnvironment() {
		return $this->_environments;
	}

	/**
	 * @param string $environment
	 * @return boolean
	 */
	public function isEnvironment($environment) {
		return in_array((string) $environment, $this->_environments);
	}

	/**
	 * @param string[]|string $environments
	 * @throws CM_Exception_Invalid
	 */
	public function setEnvironment($environments) {
		if ($this->_loaded) {
			throw new CM_Exception_Invalid('Bootloader already loaded.');
		}
		$environments = (array) $environments;
		array_walk($environments, function (&$environment) {
			$environment = (string) $environment;
		});
		$this->_environments = $environments;
	}

	/**
	 * @param string[] $functions
	 * @throws Exception
	 */
	public function load(array $functions) {
		$this->_loaded = true;
		foreach ($functions as $function) {
			if (!method_exists($this, $function)) {
				throw new Exception('Non existent bootload function `' . $function . '`');
			}
			$this->$function();
		}
	}

	public function reloadNamespacePaths() {
		$this->_namespacePaths = null;
		apc_delete($this->_getNamespacePathsCacheKey());
	}

	/**
	 * @param string $namespace
	 * @return string
	 */
	public function getNamespacePath($namespace) {
		$namespacePaths = $this->_getNamespacePaths();
		if (isset($namespacePaths[$namespace])) {
			return $namespacePaths[$namespace];
		}
		return '';
	}

	/**
	 * @return CM_ExceptionHandling_Handler_Abstract
	 */
	public function getExceptionHandler() {
		if (!$this->_exceptionHandler) {
			$isHttpRequest = !$this->isEnvironment('cli') && !$this->isEnvironment('test');
			if ($isHttpRequest) {
				$this->_exceptionHandler = new CM_ExceptionHandling_Handler_Http();
			} else {
				$this->_exceptionHandler = new CM_ExceptionHandling_Handler_Cli();
			}
		}
		return $this->_exceptionHandler;
	}

	public function errorHandler() {
		error_reporting((E_ALL | E_STRICT) & ~(E_NOTICE | E_USER_NOTICE));
		set_error_handler(array($this->getExceptionHandler(), 'handleErrorRaw'));
	}

	public function exceptionHandler() {
		$errorHandler = $this->getExceptionHandler();
		set_exception_handler(function (Exception $exception) use ($errorHandler) {
			$errorHandler->handleException($exception);
			exit(1);
		});
	}

	/**
	 * @return bool
	 */
	public function isDebug() {
		static $isDebug;
		if (null === $isDebug) {
			$isDebug = (bool) getenv('CM_DEBUG');
		}
		return $isDebug;
	}

	/**
	 * @return array
	 */
	private function _getNamespacePaths() {
		$cacheKey = $this->_getNamespacePathsCacheKey();
		if (null === $this->_namespacePaths && false === ($this->_namespacePaths = apc_fetch($cacheKey))) {
			$this->_namespacePaths = array_merge($this->_getNamespacePathsComposer(), $this->_getNamespacePathsLibrary());
			apc_store($cacheKey, $this->_namespacePaths);
		}
		return $this->_namespacePaths;
	}

	/**
	 * @return string
	 */
	private function _getNamespacePathsCacheKey() {
		return DIR_ROOT . '_CM_NamespacesPaths';
	}

	/**
	 * @return array
	 */
	private function _getNamespacePathsLibrary() {
		$namespacePaths = array();
		if (DIR_LIBRARY) {
			$directory = dir(DIR_ROOT . DIR_LIBRARY);
			while (false !== ($entry = $directory->read())) {
				if (substr($entry, 0, 1) !== '.') {
					$namespacePaths[$entry] = DIR_LIBRARY . $entry . '/';
				}
			}
		}
		return $namespacePaths;
	}

	/**
	 * @return array
	 */
	private function _getNamespacePathsComposer() {
		$namespacePaths = array();
		$composerFilePath = DIR_ROOT . 'composer.json';
		if (!CM_File::exists($composerFilePath)) {
			return $namespacePaths;
		}
		$composerJson = file_get_contents($composerFilePath);
		$composerJson = json_decode($composerJson);
		$vendorDir = 'vendor/';
		if (isset($composerJson->config) && isset($composerJson->config['vendor-dir'])) {
			$vendorDir = preg_replace('#/?$#', '/', $composerJson->config['vendor-dir']);
		}
		foreach ((array) $composerJson->require as $path => $version) {
			if (false !== strpos($path, '/')) {
				$parts = explode('/', $path);
				$namespace = $parts[1];
				$namespacePaths[$namespace] = $vendorDir . $path . '/';
			}
		}
		return $namespacePaths;
	}

	/**
	 * @return CM_Bootloader
	 * @throws Exception
	 */
	public static function getInstance() {
		if (!self::$_instance) {
			throw new Exception('No bootloader instance');
		}
		return self::$_instance;
	}
}
