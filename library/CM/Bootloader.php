<?php

class CM_Bootloader {

	public function defaults() {
		date_default_timezone_set(Config::get()->time_zone);
		mb_internal_encoding('UTF-8');
	}

	public function session() {
		CM_Session::getInstance()->start();
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
			$showError = DEBUG_MODE || IS_CRON || IS_TEST;

			if (!IS_CRON) {
				header('HTTP/1.1 500 Internal Server Error');
			}

			$class = get_class($exception);
			$code = $exception->getCode();
			if ($exception instanceof CM_Exception) {
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
		error_reporting(E_ALL & ~E_NOTICE & ~E_USER_NOTICE);
		set_error_handler(function($errno, $errstr, $errfile, $errline) {
			if (!(error_reporting() & $errno)) {
				// This error code is not included in error_reporting
				$atSign = (0 === error_reporting()); // http://php.net/manual/en/function.set-error-handler.php
				if (!$atSign && DEBUG_MODE) {
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
		define('DEBUG_MODE', (bool) Config::get()->debug && !IS_TEST);

		define('SITE_URL', Config::get()->site_url);

		define('DIR_SITE_ROOT', dirname(dirname(dirname(__FILE__))) . '/');
		define('DIR_LIBRARY', DIR_SITE_ROOT . 'library' . DIRECTORY_SEPARATOR);
		define('DIR_PUBLIC', DIR_SITE_ROOT . 'public' . DIRECTORY_SEPARATOR);
		define('DIR_LAYOUT', DIR_SITE_ROOT . 'layout' . DIRECTORY_SEPARATOR);

		define('DIR_DATA', DIR_SITE_ROOT . 'data' . DIRECTORY_SEPARATOR);
		define('DIR_DATA_LOCKS', DIR_DATA . 'locks' . DIRECTORY_SEPARATOR);

		define('DIR_TMP', DIR_SITE_ROOT . 'tmp' . DIRECTORY_SEPARATOR);
		define('DIR_TMP_SMARTY', DIR_TMP . 'smarty' . DIRECTORY_SEPARATOR);
		define('DIR_TMP_OPENINVITER', DIR_TMP . 'openinviter' . DIRECTORY_SEPARATOR);

		define('DIR_ADMIN', DIR_PUBLIC . 'admin' . DIRECTORY_SEPARATOR);
		define('DIR_ADMIN_INC', DIR_ADMIN . 'inc' . DIRECTORY_SEPARATOR);

		define('URL_OBJECTS', isset(Config::get()->objects_cdn) ? Config::get()->objects_cdn : SITE_URL);
		define('URL_CONTENT', isset(Config::get()->content_cdn) ? Config::get()->content_cdn : SITE_URL);

		define('URL_STATIC', URL_OBJECTS . 'static/');

		define('URL_ADMIN', SITE_URL . 'admin/');
		define('URL_ADMIN_CSS', URL_ADMIN . 'css/');
		define('URL_ADMIN_JS', URL_ADMIN . 'js/');
		define('URL_ADMIN_IMG', URL_ADMIN . 'img/');

		define('DIR_USERFILES', DIR_PUBLIC . 'userfiles' . DIRECTORY_SEPARATOR);
		define('URL_USERFILES', URL_CONTENT . 'userfiles/');

		define('DIR_TMP_USERFILES', DIR_USERFILES . 'tmp' . DIRECTORY_SEPARATOR);
		define('URL_TMP_USERFILES', SITE_URL . 'userfiles/tmp/');

		define('DIR_USERFILES_TEXTFORMATTER', DIR_USERFILES . 'formatter' . DIRECTORY_SEPARATOR);
		define('URL_USERFILES_TEXTFORMATTER', URL_USERFILES . 'formatter/');

		define('DIR_CONTACT_GRABBER', DIR_LIBRARY . 'ContactGrabber' . DIRECTORY_SEPARATOR);
		define('DIR_PHPMAILER', DIR_LIBRARY . 'phpmailer' . DIRECTORY_SEPARATOR);
		define('DIR_SMARTY', DIR_LIBRARY . 'Smarty' . DIRECTORY_SEPARATOR);
	}

	public function constantsTbl() {
		define('TBL_CM_SMILEY', 'cm_smiley');
		define('TBL_CM_SMILEYSET', 'cm_smileySet');
		define('TBL_CM_USER', 'cm_user');
		define('TBL_CM_USER_ONLINE', 'cm_user_online');
		define('TBL_CM_USER_PREFERENCE', 'cm_user_preference');
		define('TBL_CM_USER_PREFERENCEDEFAULT', 'cm_user_preferenceDefault');
		define('TBL_CM_USERAGENT', 'cm_useragent');
		define('TBL_CM_LOG', 'cm_log');
		define('TBL_CM_IPBLOCKED', 'cm_ipBlocked');

		define('TBL_CONFIG', 'config');
		define('TBL_CONFIG_SECTION', 'config_section');

		define('TBL_LANG', 'cm_lang');
		define('TBL_LANG_KEY', 'cm_langKey');
		define('TBL_LANG_SECTION', 'cm_langSection');
		define('TBL_LANG_VALUE', 'cm_langValue');

		define('TBL_COOKIES_LOGIN', 'cookies_login');

		define('TBL_PROFILE_FIELD_KEY', 'profile_field_key');
		define('TBL_PROFILE_FIELD_VALUE', 'profile_field_value');

		define('TBL_PROFILE_FIELD', 'profile_field');

		define('TBL_LOCATION_COUNTRY', 'cm_locationCountry');
		define('TBL_LOCATION_STATE', 'cm_locationState');
		define('TBL_LOCATION_CITY', 'cm_locationCity');
		define('TBL_LOCATION_ZIP', 'cm_locationZip');
		define('TBL_LOCATION_CITY_IP', 'cm_locationCityIp');
		define('TBL_LOCATION_COUNTRY_IP', 'cm_locationCountryIp');

		define('TBL_PAYMENT_PROVIDER', 'payment_provider');
		define('TBL_PAYMENT_PROVIDER_FIELD', 'payment_provider_field');
		define('TBL_PAYMENT_PROVIDER_BUNDLE', 'payment_provider_bundle');

		define('TBL_PROFILE_EMAIL_VERIFY_CODE', 'profile_email_verification_code');

		define('TBL_PROFILE', 'profile');
		define('TBL_PROFILE_PHOTO', 'profile_photo');
		define('TBL_PROFILE_VIDEO', 'profile_video');
		define('TBL_PROFILE_VIDEO_RATE', 'profile_video_rate');
		define('TBL_VIDEO_VIEW', 'video_view');
		define('TBL_VIDEO_CATEGORY', 'video_category');

		define('TBL_ADMIN', 'admin');
		define('TBL_LINK_ADMIN_DOCUMENT', 'link_admin_document');

		define('TBL_CAPTCHA', 'tmp_captcha');

		define('TBL_BLOG_POST', 'blogpost');

		define('TBL_COMMENT', 'comment');

		define('TBL_BLOG_POST_TAG', 'tag_4');
		define('TBL_VIDEO_TAG', 'tag_3');

		define('TBL_BLOG_POST_RATE', 'rate_4');
		define('TBL_PHOTO_RATE', 'rate_2');
		define('TBL_VIDEO_RATE', 'rate_3');
		define('TBL_PROFILE_RATE', 'profile_rate');

		define('TBL_TAG', 'tag');

		define('TBL_FEED', 'feed');

		define('TBL_PHOTO_VIEW', 'profile_photo_view');

		define('TBL_BADWORD', 'badword');

		define('TBL_VIRTUAL_GIFT_TPL', 'virtual_gift_template');

		define('TBL_TEXT_FORMATTER_IMAGE', 'text_formatter_image');

		define('TBL_REVIEW', 'review');
		define('TBL_PROFILE_RESETPASSWORD', 'profile_resetpassword');

		define('TBL_TMP_PHOTO', 'tmp_photo');
		define('TBL_TMP_USER', 'tmp_user');
		define('TBL_TMP_VIDEO', 'tmp_video');
		define('TBL_TMP_BLOGPOST', 'tmp_blogpost');
		define('TBL_TMP_LOCATION', 'tmp_location');
		define('TBL_TMP_USERFILE', 'tmp_userfile');

		define('TBL_DEV_STREAM', 'dev_stream');

		define('TBL_CHAT', 'chat');
		define('TBL_CHAT_MEMBER', 'chat_member');
		define('TBL_CHAT_MESSAGE', 'chat_message');

		define('TBL_CONTENTLIST', 'cm_contentlist');

		define('TBL_ACTION', 'action');
		define('TBL_ACTION_LIMIT', 'actionLimit');

		define('TBL_CONVERSATION', 'conversation');
		define('TBL_CONVERSATION_MESSAGE', 'conversation_message');
		define('TBL_CONVERSATION_RECIPIENT', 'conversation_recipient');

		define('TBL_SESSION', 'cm_session');

		define('TBL_REPORT', 'report');

		define('TBL_EMAIL_QUEUE', 'email_queue');
		define('TBL_MAILING', 'mailing');

		define('TBL_ROLE', 'role');
		define('TBL_ROLE_ADMIN', 'role_admin');

		define('TBL_SVM', 'svm');
		define('TBL_SVM_TRAINING', 'svm_training');

		define('TBL_TRANSACTION_SERVICEBUNDLE', 'transaction_serviceBundle');

		define('TBL_SERVICEBUNDLE', 'serviceBundle');
		define('TBL_SERVICEBUNDLE_SERVICE', 'serviceBundle_service');
		define('TBL_SERVICE', 'service');
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
