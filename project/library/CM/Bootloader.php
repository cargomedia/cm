<?php

class CM_Bootloader {

	public function defaults() {
		date_default_timezone_set(CM_Config::get()->timeZone);
		mb_internal_encoding('UTF-8');
	}

	public function autoloader() {
		spl_autoload_register(function($className) {
			$path = DIR_ROOT . 'library/' . str_replace('_', '/', $className) . '.php';
			if (is_file($path)) {
				require_once $path;
				return;
			}
		});
	}

	public function exceptionHandler() {
		set_exception_handler(function(Exception $exception) {
			$showError = IS_DEBUG || IS_CRON || IS_TEST;

			if (!IS_CRON && !IS_TEST) {
				header('HTTP/1.1 500 Internal Server Error');
			}

			$class = get_class($exception);
			$code = $exception->getCode();
			if ($exception instanceof CM_Exception) {
				/** @var CM_Exception $exception */
				$msg = $exception->getMessagePublic();
			} else {
				$msg = 'Internal server error';
			}

			if ($showError) {
				$msg = $class . ' (' . $code . '): <b>' . $exception->getMessage() . '</b><br/>';
				$msg .= 'Thrown in: <b>' . $exception->getFile() . '</b> on line <b>' . $exception->getLine() . '</b>:<br/>';
				$msg .= '<div style="margin: 2px 6px;">' . nl2br($exception->getTraceAsString()) . '</div>';
			}

			$logMsg = $class . ' (' . $code . '): ' . $exception->getMessage() . PHP_EOL;
			$logMsg .= '## ' . $exception->getFile() . '(' . $exception->getLine() . '):' . PHP_EOL;
			$logMsg .= $exception->getTraceAsString() . PHP_EOL;

			$log = new CM_Paging_Log_Error();
			$log->add($logMsg);
			echo $msg;
			exit(1);
		});
	}

	public function errorHandler() {
		error_reporting((E_ALL | E_STRICT) & ~(E_NOTICE | E_USER_NOTICE));
		set_error_handler(function($errno, $errstr, $errfile, $errline) {
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
		defined('IS_TEST') || define('IS_TEST', false);
		defined('IS_CRON') || define('IS_CRON', false);
		define('IS_DEBUG', (bool) CM_Config::get()->debug && !IS_TEST);
		define('URL_ROOT', !empty(CM_Config::get()->urlRoot) ? CM_Config::get()->urlRoot : (
				'http://' . (isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'localhost') .
						((isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] != 80) ? ':' . $_SERVER['SERVER_PORT'] : '') . '/'));

		define('DIR_SITE_ROOT', dirname(dirname(__DIR__)) . '/');
		define('DIR_LIBRARY', DIR_SITE_ROOT . 'library' . DIRECTORY_SEPARATOR);
		define('DIR_PUBLIC', DIR_SITE_ROOT . 'public' . DIRECTORY_SEPARATOR);
		define('DIR_LAYOUT', DIR_SITE_ROOT . 'layout' . DIRECTORY_SEPARATOR);

		define('DIR_DATA', !empty(CM_Config::get()->dirData) ? CM_Config::get()->dirData : DIR_SITE_ROOT . 'data' . DIRECTORY_SEPARATOR);
		define('DIR_DATA_LOCKS', DIR_DATA . 'locks' . DIRECTORY_SEPARATOR);

		define('DIR_TMP', !empty(CM_Config::get()->dirTmp) ? CM_Config::get()->dirTmp : DIR_SITE_ROOT . 'tmp' . DIRECTORY_SEPARATOR);
		define('DIR_TMP_SMARTY', DIR_TMP . 'smarty' . DIRECTORY_SEPARATOR);

		define('URL_OBJECTS', !empty(CM_Config::get()->urlCdnObjects) ? CM_Config::get()->urlCdnObjects : URL_ROOT);
		define('URL_CONTENT', !empty(CM_Config::get()->urlCdnContent) ? CM_Config::get()->urlCdnContent : URL_ROOT);

		define('URL_STATIC', URL_OBJECTS . 'static/');

		define('DIR_USERFILES', !empty(CM_Config::get()->dirUserfiles) ? CM_Config::get()->dirUserfiles :
				DIR_PUBLIC . 'userfiles' . DIRECTORY_SEPARATOR);
		define('URL_USERFILES', URL_CONTENT . 'userfiles/');

		define('DIR_TMP_USERFILES', DIR_USERFILES . 'tmp' . DIRECTORY_SEPARATOR);
		define('URL_TMP_USERFILES', URL_ROOT . 'userfiles/tmp/');

		define('DIR_PHPMAILER', DIR_LIBRARY . 'phpmailer' . DIRECTORY_SEPARATOR);
		define('DIR_SMARTY', DIR_LIBRARY . 'Smarty' . DIRECTORY_SEPARATOR);

		define('TBL_CM_SMILEY', 'cm_smiley');
		define('TBL_CM_SMILEYSET', 'cm_smileySet');
		define('TBL_CM_USER', 'cm_user');
		define('TBL_CM_USER_ONLINE', 'cm_user_online');
		define('TBL_CM_USER_PREFERENCE', 'cm_user_preference');
		define('TBL_CM_USER_PREFERENCEDEFAULT', 'cm_user_preferenceDefault');
		define('TBL_CM_USERAGENT', 'cm_useragent');
		define('TBL_CM_LOG', 'cm_log');
		define('TBL_CM_IPBLOCKED', 'cm_ipBlocked');

		define('TBL_CM_LANG', 'cm_lang');
		define('TBL_CM_LANG_KEY', 'cm_langKey');
		define('TBL_CM_LANG_SECTION', 'cm_langSection');
		define('TBL_CM_LANG_VALUE', 'cm_langValue');

		define('TBL_CM_LOCATIONCOUNTRY', 'cm_locationCountry');
		define('TBL_CM_LOCATIONSTATE', 'cm_locationState');
		define('TBL_CM_LOCATIONCITY', 'cm_locationCity');
		define('TBL_CM_LOCATIONZIP', 'cm_locationZip');
		define('TBL_CM_LOCATIONCITYIP', 'cm_locationCityIp');
		define('TBL_CM_LOCATIONCOUNTRYIP', 'cm_locationCountryIp');

		define('TBL_CM_TMP_LOCATION', 'cm_tmp_location');
		define('TBL_CM_TMP_USERFILE', 'cm_tmp_userfile');

		define('TBL_CM_CAPTCHA', 'cm_captcha');

		define('TBL_CM_STREAM', 'cm_stream');

		define('TBL_CM_STRING', 'cm_string');

		define('TBL_CM_ACTION', 'cm_action');
		define('TBL_CM_ACTIONLIMIT', 'cm_actionLimit');

		define('TBL_CM_SESSION', 'cm_session');

		define('TBL_CM_MAIL', 'cm_mail');

		define('TBL_CM_ROLE', 'cm_role');

		define('TBL_CM_SVM', 'cm_svm');
		define('TBL_CM_SVMTRAINING', 'cm_svmtraining');

		define('TBL_CM_OPTION', 'cm_option');
	}

	/**
	 * @param string[] $functions
	 * @throws Exception
	 */
	public static function load(array $functions) {
		foreach ($functions as $function) {
			$instance = new static();
			if (!method_exists($instance, $function)) {
				throw new Exception('Non existent bootload function `' . $function . '`');
			}
			$instance->$function();
		}
	}
}
