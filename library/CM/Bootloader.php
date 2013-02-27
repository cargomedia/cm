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
	}

	public function autoloader() {
		$composerAutoloader = DIR_ROOT . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
		if (is_file($composerAutoloader)) {
			require_once $composerAutoloader;
		}

		spl_autoload_register(function ($className) {
			$relativePath = str_replace('_', '/', $className) . '.php';
			$path = CM_Util::getNamespacePath(CM_Util::getNamespace($className, true)) . 'library/' . $relativePath;
			if (is_file($path)) {
				require_once $path;
				return;
			}
		});
	}

	public function exceptionHandler() {
		set_exception_handler(function(Exception $exception) {
			if (!headers_sent()) {
				header('Content-Type: text/plain');
			}
			CM_Bootloader::handleException($exception);
			exit(1);
		});
	}

	public function errorHandler() {
		error_reporting((E_ALL | E_STRICT) & ~(E_NOTICE | E_USER_NOTICE));
		set_error_handler(function ($errno, $errstr, $errfile, $errline) {
			if (!(error_reporting() & $errno)) {
				// This error code is not included in error_reporting
				$atSign = (0 === error_reporting()); // http://php.net/manual/en/function.set-error-handler.php
				if (!$atSign && IS_DEBUG) {
					$errorCodes = array(E_ERROR => 'E_ERROR', E_WARNING => 'E_WARNING', E_PARSE => 'E_PARSE', E_NOTICE => 'E_NOTICE',
						E_CORE_ERROR => 'E_CORE_ERROR', E_CORE_WARNING => 'E_CORE_WARNING', E_COMPILE_ERROR => 'E_COMPILE_ERROR',
						E_COMPILE_WARNING => 'E_COMPILE_WARNING', E_USER_ERROR => 'E_USER_ERROR', E_USER_WARNING => 'E_USER_WARNING',
						E_USER_NOTICE => 'E_USER_NOTICE', E_STRICT => 'E_STRICT', E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
						E_DEPRECATED => 'E_DEPRECATED', E_USER_DEPRECATED => 'E_USER_DEPRECATED', E_ALL => 'E_ALL');
					$errstr = $errorCodes[$errno] . ': ' . $errstr;
					CM_Debug::get()->addError($errfile, $errline, $errstr);
				}
				return true;
			}
			throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
		});
	}

	public function constants() {
		define('DIR_VENDOR', DIR_ROOT . 'vendor' . DIRECTORY_SEPARATOR);
		define('DIR_PUBLIC', DIR_ROOT . 'public' . DIRECTORY_SEPARATOR);

		define('IS_DEBUG', (bool) CM_Config::get()->debug && !CM_Bootloader::getInstance()->isEnvironment('test'));

		define('DIR_DATA', !empty(CM_Config::get()->dirData) ? CM_Config::get()->dirData : DIR_ROOT . 'data' . DIRECTORY_SEPARATOR);
		define('DIR_DATA_LOCKS', DIR_DATA . 'locks' . DIRECTORY_SEPARATOR);
		define ('DIR_DATA_LOG', DIR_DATA . 'logs' . DIRECTORY_SEPARATOR);

		define('DIR_TMP', !empty(CM_Config::get()->dirTmp) ? CM_Config::get()->dirTmp : DIR_ROOT . 'tmp' . DIRECTORY_SEPARATOR);
		define('DIR_TMP_SMARTY', DIR_TMP . 'smarty' . DIRECTORY_SEPARATOR);

		define('DIR_USERFILES', !empty(CM_Config::get()->dirUserfiles) ? CM_Config::get()->dirUserfiles :
				DIR_PUBLIC . 'userfiles' . DIRECTORY_SEPARATOR);

		define('TBL_CM_SMILEY', 'cm_smiley');
		define('TBL_CM_SMILEYSET', 'cm_smileySet');
		define('TBL_CM_USER', 'cm_user');
		define('TBL_CM_USER_ONLINE', 'cm_user_online');
		define('TBL_CM_USER_PREFERENCE', 'cm_user_preference');
		define('TBL_CM_USER_PREFERENCEDEFAULT', 'cm_user_preferenceDefault');
		define('TBL_CM_USERAGENT', 'cm_useragent');
		define('TBL_CM_LOG', 'cm_log');
		define('TBL_CM_IPBLOCKED', 'cm_ipBlocked');

		define('TBL_CM_LANGUAGE', 'cm_language');
		define('TBL_CM_LANGUAGEKEY', 'cm_languageKey');
		define('TBL_CM_LANGUAGEKEY_VARIABLE', 'cm_languageKey_variable');
		define('TBL_CM_LANGUAGEVALUE', 'cm_languageValue');

		define('TBL_CM_LOCATIONCOUNTRY', 'cm_locationCountry');
		define('TBL_CM_LOCATIONSTATE', 'cm_locationState');
		define('TBL_CM_LOCATIONCITY', 'cm_locationCity');
		define('TBL_CM_LOCATIONZIP', 'cm_locationZip');
		define('TBL_CM_LOCATIONCITYIP', 'cm_locationCityIp');
		define('TBL_CM_LOCATIONCOUNTRYIP', 'cm_locationCountryIp');

		define('TBL_CM_TMP_LOCATION', 'cm_tmp_location');
		define('TBL_CM_TMP_USERFILE', 'cm_tmp_userfile');

		define('TBL_CM_CAPTCHA', 'cm_captcha');

		define('TBL_CM_STRING', 'cm_string');

		define('TBL_CM_ACTION', 'cm_action');
		define('TBL_CM_ACTIONLIMIT', 'cm_actionLimit');

		define('TBL_CM_SESSION', 'cm_session');
		define('TBL_CM_REQUESTCLIENT', 'cm_requestClient');

		define('TBL_CM_MAIL', 'cm_mail');

		define('TBL_CM_ROLE', 'cm_role');

		define('TBL_CM_SVM', 'cm_svm');
		define('TBL_CM_SVMTRAINING', 'cm_svmtraining');

		define('TBL_CM_SPLITFEATURE', 'cm_splitfeature');
		define('TBL_CM_SPLITFEATURE_FIXTURE', 'cm_splitfeature_fixture');

		define('TBL_CM_SPLITTEST', 'cm_splittest');
		define('TBL_CM_SPLITTESTVARIATION', 'cm_splittestVariation');
		define('TBL_CM_SPLITTESTVARIATION_FIXTURE', 'cm_splittestVariation_fixture');

		define('TBL_CM_OPTION', 'cm_option');

		define('TBL_CM_STREAM_PUBLISH', 'cm_stream_publish');
		define('TBL_CM_STREAM_SUBSCRIBE', 'cm_stream_subscribe');
		define('TBL_CM_STREAMCHANNEL', 'cm_streamChannel');
		define('TBL_CM_STREAMCHANNEL_VIDEO', 'cm_streamChannel_video');

		define('TBL_CM_STREAMCHANNELARCHIVE_VIDEO', 'cm_streamChannelArchive_video');
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
		if (!file_exists($composerFilePath)) {
			return $namespacePaths;
		}
		$composerJson = file_get_contents($composerFilePath);
		$composerJson = json_decode($composerJson);
		$vendorDir = 'vendor/';
		if (isset($composerJson->config) && isset($composerJson->config['vendor-dir'])) {
			$vendorDir = preg_replace('#/?$#', '/', $composerJson->config['vendor-dir']);
		}
		foreach ((array) $composerJson->require as $path => $version) {
			$parts = explode('/', $path);
			$namespace = $parts[1];
			$namespacePaths[$namespace] = $vendorDir . $path . '/';
		}
		return $namespacePaths;
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
	 * @return CM_Bootloader
	 * @throws Exception
	 */
	public static function getInstance() {
		if (!self::$_instance) {
			throw new Exception('No bootloader instance');
		}
		return self::$_instance;
	}

	/**
	 * @param Exception                      $exception
	 * @param CM_OutputStream_Interface|null $output
	 */
	public static function handleException(Exception $exception, CM_OutputStream_Interface $output = null) {
		if (null === $output) {
			$output = new CM_OutputStream_Stream_Output();
		}
		$exceptionFormatter = function (Exception $exception) {
			$text = get_class($exception) . ' (' . $exception->getCode() . '): ' . $exception->getMessage() . PHP_EOL;
			$text .= '## ' . $exception->getFile() . '(' . $exception->getLine() . '):' . PHP_EOL;
			$text .= $exception->getTraceAsString() . PHP_EOL;
			return $text;
		};

		$showError = IS_DEBUG || CM_Bootloader::getInstance()->isEnvironment('cli') || CM_Bootloader::getInstance()->isEnvironment('test');
		if (!CM_Bootloader::getInstance()->isEnvironment('cli') && !CM_Bootloader::getInstance()->isEnvironment('test')) {
			header('HTTP/1.1 500 Internal Server Error');
		}

		try {
			if ($exception instanceof CM_Exception) {
				$log = $exception->getLog();
			} else {
				$log = new CM_Paging_Log_Error();
			}
			$log->add($exceptionFormatter($exception));
		} catch (Exception $loggerException) {
			$logEntry = '[' . date('d.m.Y - H:i:s', time()) . ']' . PHP_EOL;
			$logEntry .= '### Cannot log error: ' . PHP_EOL;
			$logEntry .= $exceptionFormatter($loggerException);
			$logEntry .= '### Original Exception: ' . PHP_EOL;
			$logEntry .= $exceptionFormatter($exception) . PHP_EOL;
			file_put_contents(DIR_DATA_LOG . 'error.log', $logEntry, FILE_APPEND);
		}

		if ($showError) {
			$output->writeln(get_class($exception) . ' (' . $exception->getCode() . '): ' . $exception->getMessage());
			$output->writeln('Thrown in: ' . $exception->getFile() . ':' . $exception->getLine());
			$output->writeln($exception->getTraceAsString());
		} else {
			$output->writeln('Internal server error');
		}
	}
}
