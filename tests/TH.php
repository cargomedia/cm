<?php
require_once 'TH/Page.php';

/**
 * TH - TestHelper-class with static convenience-methods
 */

class TH {
	private static $timeDelta = 0;
	private static $initialized = false;

	public static function init() {
		if (self::$initialized) {
			return;
		}

		// Setup
		define('DIR_TESTS', __DIR__ . DIRECTORY_SEPARATOR);
		define('DIR_TEST_DATA', DIR_TESTS . 'data' . DIRECTORY_SEPARATOR);
		define('IS_TEST', true);

		define('DIR_ROOT', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'project' . DIRECTORY_SEPARATOR);
		require_once DIR_ROOT . 'library/CM/Bootloader.php';
		CM_Bootloader::load(array('Autoloader', 'constants', 'exceptionHandler', 'errorHandler', 'defaults'));

		!is_dir(DIR_DATA) ? mkdir(DIR_DATA) : null;
		!is_dir(DIR_USERFILES) ? mkdir(DIR_USERFILES) : null;
		!is_dir(DIR_TMP_USERFILES) ? mkdir(DIR_TMP_USERFILES) : null;

		// Import db
		self::_runSql('DROP DATABASE IF EXISTS ' . CM_Config::get()->CM_Mysql->db);
		self::_runSql('CREATE DATABASE ' . CM_Config::get()->CM_Mysql->db);
		self::_loadDb(DIR_TEST_DATA . 'db/dump.sql', CM_Config::get()->CM_Mysql->db);

		// Reset environment
		self::clearEnv();
		self::timeInit();

		self::$initialized = true;
	}

	public static function clearEnv() {
		self::clearDb();
		self::clearCache();
		self::timeReset();
	}

	public static function clearCache() {
		CM_Cache::flush();
		CM_CacheLocal::flush();
	}

	public static function clearDb() {
		$alltables = CM_Mysql::query('SHOW TABLES')->fetchCol();
		foreach ($alltables as $table) {
			CM_Mysql::truncate($table);
		}
	}

	private static function _runCmd($cmd) {
		exec($cmd, $output, $return_status);
		if ($return_status != 0) {
			exit(1);
		}
		return $output;
	}

	private static function _runSql($sql, $dbName = null) {
		$cmd = 'mysql -u' . CM_Config::get()->CM_Mysql->user . ' -p' . CM_Config::get()->CM_Mysql->pass . ' -h' .
				CM_Config::get()->CM_Mysql->server['host'] . ' -P ' . CM_Config::get()->CM_Mysql->server['port'];
		if ($dbName) {
			$cmd .= ' ' . $dbName;
		}
		$cmd .= ' -s -e"' . $sql . '"';
		return self::_runCmd($cmd);
	}

	private static function _loadDb($sqlFile, $dbName) {
		$cmd = 'mysql -u' . CM_Config::get()->CM_Mysql->user . ' -p' . CM_Config::get()->CM_Mysql->pass . ' -h' .
				CM_Config::get()->CM_Mysql->server['host'] . ' -P ' . CM_Config::get()->CM_Mysql->server['port'] . ' ' . $dbName . ' < ' . $sqlFile;
		return self::_runCmd($cmd);
	}

	public static function timeInit() {
		runkit_function_copy('time', 'time_original');
		runkit_function_redefine('time', '', 'return TH::time();');
	}

	public static function time() {
		return time_original() + self::$timeDelta;
	}

	public static function timeForward($sec) {
		self::$timeDelta += $sec;
		self::clearCache();
	}

	public static function timeDaysForward($days) {
		self::timeForward($days * 24 * 60 * 60);
	}

	public static function timeReset() {
		self::$timeDelta = 0;
		self::clearCache();
	}

	public static function timeDelta() {
		return self::$timeDelta;
	}

	public static function timeDiffInDays($stamp1, $stamp2) {
		return round(($stamp2 - $stamp1) / (60 * 60 * 24));
	}

	/**
	 * @return CM_Model_User
	 */
	public static function createUser() {
		return CM_Model_User::create();
	}

	/**
	 * @param CM_Component_Abstract $component
	 * @param CM_Model_User		 $viewer OPTIONAL
	 * @return TH_Page
	 */
	public static function renderComponent(CM_Component_Abstract $component, CM_Model_User $viewer = null) {
		$render = new CM_Render();
		$component->setViewer($viewer);
		$component->checkAccessible();
		$component->prepare();
		$componentHtml = $render->render($component);
		$html = '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head><body>' . $componentHtml . '</body></html>';
		return new TH_Page($html);
	}

	/**
	 * @param string			 $pageClass
	 * @param CM_Model_User|null $viewer OPTIONAL
	 * @param array			  $params OPTIONAL
	 * @return CM_Page_Abstract
	 */
	public static function createPage($pageClass, CM_Model_User $viewer = null, $params = array()) {
		$request = new CM_Request_Get('?' . http_build_query($params), array(), $viewer);
		return new $pageClass($request);
	}

	/**
	 * @param CM_Page_Abstract $page
	 * @return TH_Page
	 */
	public static function renderPage(CM_Page_Abstract $page) {
		$render = new CM_Render();
		$response = new CM_Response_Page($page->getRequest());
		$page->prepare($response);
		$html = $render->render($page);
		return new TH_Page($html);
	}

	/**
	 * @param CM_Form_Abstract	  $form
	 * @param CM_FormField_Abstract $formField
	 * @param array				 $params OPTIONAL
	 * @return TH_Page
	 */
	public static function renderFormField(CM_Form_Abstract $form, CM_FormField_Abstract $formField, array $params = array()) {
		$render = new CM_Render();
		$formField->prepare($params);
		$html = $render->render($formField, array('form' => $form));
		return new TH_Page($html);
	}

	/**
	 * @param CM_Model_User|null $user
	 * @return CM_Session
	 */
	public static function createSession(CM_Model_User $user = null) {
		if (is_null($user)) {
			$user = TH::createUser();
		}
		$session = new CM_Session();
		$session->setUser($user);
		return $session;
	}

	/**
	 * @param int|null $type
	 * @return CM_Model_StreamChannel_Abstract
	 * @throws CM_Exception_Invalid
	 */
	public static function createStreamChannel($type = null) {
		if (is_null($type)) {
			$type = CM_Model_StreamChannel_Video::TYPE;
		}
		return CM_Model_StreamChannel_Abstract::createType($type, array('key' => rand(1, 10000) . '_' . rand(1, 100)));
	}

	/**
	 * @param CM_Model_User|null $user
	 * @return CM_Model_Stream_Publish
	 */
	public static function createStreamPublish(CM_Model_User $user = null, CM_Model_StreamChannel_Abstract $streamChannel = null) {
		if (!$user) {
			$user = TH::createUser();
		}
		if (is_null($streamChannel)) {
			$streamChannel = TH::createStreamChannel();
		}
		return $streamChannel->getStreamPublishs()->add(array('user' => $user, 'start' => time(), 'allowedUntil' => time() + 100,
			'price' => rand(10, 50) / 10, 'key' => rand(1, 10000) . '_' . rand(1, 100)));
	}

	/**
	 * @param CM_Model_User|null				 $user
	 * @param CM_Model_Stream_Publish|null	   $streamPublish
	 * @return CM_Model_Stream_Subscribe
	 */
	public static function createStreamSubscribe(CM_Model_User $user = null, CM_Model_StreamChannel_Abstract $streamChannel = null) {
		if (!$user) {
			$user = TH::createUser();
		}
		if (is_null($streamChannel)) {
			$streamChannel = TH::createStreamChannel();
		}
		return $streamChannel->getStreamSubscribes()->add(array('user' => $user, 'start' => time(), 'allowedUntil' => time() + 100,
			'key' => rand(1, 10000) . '_' . rand(1, 100)));
	}

	/**
	 * @param int	$length
	 * @param string $charset
	 * @return string
	 */
	private static function _randStr($length, $charset = 'abcdefghijklmnopqrstuvwxyz0123456789') {
		$str = '';
		$count = strlen($charset);
		while ($length--) {
			$str .= $charset[mt_rand(0, $count - 1)];
		}
		return $str;
	}
}
