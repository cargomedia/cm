<?php

class CM_Bootloader {

	public function defaults() {
		date_default_timezone_set(CM_Config::get()->timeZone);
		mb_internal_encoding('UTF-8');
		umask(0);
	}

	public function autoloader() {
		$includePath =  DIR_ROOT . 'library/' . PATH_SEPARATOR . ini_get('include_path');
		ini_set('include_path', $includePath);
		$includePaths = explode(PATH_SEPARATOR, $includePath);
		spl_autoload_register(function($className) use ($includePaths) {
			$relativePath = str_replace('_', '/', $className) . '.php';
			foreach ($includePaths as $includePath) {
				$path = $includePath . $relativePath;
				if (is_file($path)) {
					require_once $path;
					return;
				}
			}
		});
	}

	public function exceptionHandler() {
		$exceptionFormatter = function(Exception $exception) {
			$text = get_class($exception) . ' (' . $exception->getCode() . '): ' . $exception->getMessage() . PHP_EOL;
			$text .= '## ' . $exception->getFile() . '(' . $exception->getLine() . '):' . PHP_EOL;
			$text .= $exception->getTraceAsString() . PHP_EOL;
			return $text;
		};

		set_exception_handler(function(Exception $exception) use($exceptionFormatter) {
			$showError = IS_DEBUG || IS_CRON || IS_TEST;

			if (!IS_CRON && !IS_TEST) {
				header('HTTP/1.1 500 Internal Server Error');
			}

			try {
				$log = new CM_Paging_Log_Error();
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
				echo get_class($exception) . ' (' . $exception->getCode() . '): <b>' . $exception->getMessage() . '</b><br/>';
				echo 'Thrown in: <b>' . $exception->getFile() . '</b> on line <b>' . $exception->getLine() . '</b>:<br/>';
				echo '<div style="margin: 2px 6px;">' . nl2br($exception->getTraceAsString()) . '</div>';
			} else {
				echo 'Internal server error';
			}
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

		define('DIR_SITE_ROOT', dirname(dirname(__DIR__)) . '/');
		define('DIR_LIBRARY', DIR_SITE_ROOT . 'library' . DIRECTORY_SEPARATOR);
		define('DIR_PUBLIC', DIR_SITE_ROOT . 'public' . DIRECTORY_SEPARATOR);
		define('DIR_LAYOUT', DIR_SITE_ROOT . 'layout' . DIRECTORY_SEPARATOR);

		define('DIR_DATA', !empty(CM_Config::get()->dirData) ? CM_Config::get()->dirData : DIR_SITE_ROOT . 'data' . DIRECTORY_SEPARATOR);
		define('DIR_DATA_LOCKS', DIR_DATA . 'locks' . DIRECTORY_SEPARATOR);
		define ('DIR_DATA_LOG', DIR_DATA . 'logs' . DIRECTORY_SEPARATOR);

		define('DIR_TMP', !empty(CM_Config::get()->dirTmp) ? CM_Config::get()->dirTmp : DIR_SITE_ROOT . 'tmp' . DIRECTORY_SEPARATOR);
		define('DIR_TMP_SMARTY', DIR_TMP . 'smarty' . DIRECTORY_SEPARATOR);

		define('DIR_USERFILES', !empty(CM_Config::get()->dirUserfiles) ? CM_Config::get()->dirUserfiles :
				DIR_PUBLIC . 'userfiles' . DIRECTORY_SEPARATOR);

		define('DIR_PHPMAILER', DIR_LIBRARY . 'phpmailer' . DIRECTORY_SEPARATOR);

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

		define('TBL_CM_STREAM', 'cm_stream');

		define('TBL_CM_STRING', 'cm_string');

		define('TBL_CM_ACTION', 'cm_action');
		define('TBL_CM_ACTIONLIMIT', 'cm_actionLimit');

		define('TBL_CM_SESSION', 'cm_session');

		define('TBL_CM_MAIL', 'cm_mail');

		define('TBL_CM_ROLE', 'cm_role');

		define('TBL_CM_SVM', 'cm_svm');
		define('TBL_CM_SVMTRAINING', 'cm_svmtraining');

		define('TBL_CM_SPLITTEST', 'cm_splittest');
		define('TBL_CM_SPLITTESTVARIATION', 'cm_splittestVariation');
		define('TBL_CM_SPLITTESTVARIATION_USER', 'cm_splittestVariation_user');

		define('TBL_CM_OPTION', 'cm_option');

		define('TBL_CM_STREAM_PUBLISH', 'cm_stream_publish');
		define('TBL_CM_STREAM_SUBSCRIBE', 'cm_stream_subscribe');
		define('TBL_CM_STREAMCHANNEL', 'cm_streamChannel');
		define('TBL_CM_STREAMCHANNEL_VIDEO', 'cm_streamChannel_video');

		define('TBL_CM_STREAMCHANNELARCHIVE_VIDEO', 'cm_streamChannelArchive_video');
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
