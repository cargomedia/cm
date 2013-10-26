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

	/** @var bool */
	private $_debug;

	/** @var CM_Bootloader */
	protected static $_instance;

	/** @var CM_ExceptionHandling_Handler_Abstract */
	private $_exceptionHandler;

	/**
	 * @param string      $pathRoot
	 * @param string|null $dirLibrary
	 * @throws CM_Exception_Invalid
	 */
	public function __construct($pathRoot, $dirLibrary) {
		if (self::$_instance) {
			throw new CM_Exception_Invalid('Bootloader already instantiated');
		}
		self::$_instance = $this;
		define('DIR_ROOT', $pathRoot);
		define('DIR_LIBRARY', $dirLibrary);
		$this->_debug = (bool) getenv('CM_DEBUG');
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

		define('DIR_DATA', DIR_ROOT . 'data' . DIRECTORY_SEPARATOR);
		define('DIR_DATA_LOCKS', DIR_DATA . 'locks' . DIRECTORY_SEPARATOR);
		define('DIR_DATA_LOG', DIR_DATA . 'logs' . DIRECTORY_SEPARATOR);
		define('DIR_DATA_SVM', DIR_DATA . 'svm' . DIRECTORY_SEPARATOR);

		define('DIR_TMP', DIR_ROOT . 'tmp' . DIRECTORY_SEPARATOR);
		define('DIR_TMP_CACHE',  DIR_TMP . 'cache' . DIRECTORY_SEPARATOR);
		define('DIR_TMP_SMARTY', DIR_TMP . 'smarty' . DIRECTORY_SEPARATOR);

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

	/**
	 * @return CM_ExceptionHandling_Handler_Abstract
	 */
	public function getExceptionHandler() {
		if (!$this->_exceptionHandler) {
			if ($this->isCli()) {
				$this->_exceptionHandler = new CM_ExceptionHandling_Handler_Cli();
			} else {
				$this->_exceptionHandler = new CM_ExceptionHandling_Handler_Http();
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
		return $this->_debug;
	}

	/**
	 * @return bool
	 */
	public function isCli() {
		return PHP_SAPI === 'cli';
	}

	/**
	 * @return string[]
	 */
	public function getNamespaces() {
		return array_keys($this->_getNamespacePaths());
	}

	public function reloadNamespacePaths() {
		$cacheKey = CM_CacheConst::Modules;
		$cache = new CM_Cache_Storage_Apc();
		$cache->delete($cacheKey);
	}

	/**
	 * @return array
	 */
	private function _getNamespacePaths() {
		$cacheKey = CM_CacheConst::Modules;
		$apcCache = new CM_Cache_Storage_Apc();
		if (false === ($namespacePaths = $apcCache->get($cacheKey))) {
			$fileCache = new CM_Cache_Storage_File();
			$installation = new CM_App_Installation();
			if ($installation->getUpdateStamp() > $fileCache->getCreateStamp($cacheKey) || false === ($namespacePaths = $fileCache->get($cacheKey))) {
				$namespacePaths = $installation->getModulePaths();
				$fileCache->set($cacheKey, $namespacePaths);
			}
			$apcCache->set($cacheKey, $namespacePaths);
		}
		return $namespacePaths;
	}

	/**
	 * @param string $namespace
	 * @throws CM_Exception_Invalid
	 * @return string
	 */
	public function getNamespacePath($namespace) {
		$namespacePaths = $this->_getNamespacePaths();
		if (!array_key_exists($namespace, $namespacePaths)) {
			throw new CM_Exception_Invalid('`' . $namespace . '`, not found within namespace paths');
		}
		return $namespacePaths[$namespace];
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
