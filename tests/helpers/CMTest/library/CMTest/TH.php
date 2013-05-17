<?php

class CMTest_TH {

	private static $timeDelta = 0;
	private static $initialized = false;
	private static $_configBackup;

	/** @var CM_Db_Client|null */
	private static $_dbClient = null;

	public static function init() {
		if (self::$initialized) {
			return;
		}
		$forceReload = CM_Config::get()->CMTest_TH->dropDatabase;
		CM_App::getInstance()->setupDatabase($forceReload);

		self::$_configBackup = serialize(CM_Config::get());

		// Reset environment
		self::clearEnv();
		self::randomizeAutoincrement();
		self::timeInit();

		self::$initialized = true;
	}

	public static function clearEnv() {
		self::clearDb();
		self::clearCache();
		self::timeReset();
		self::clearTmp();
		self::clearConfig();
	}

	public static function clearCache() {
		CM_Cache::flush();
		CM_CacheLocal::flush();
	}

	public static function clearDb() {
		$alltables = CM_Db_Db::exec('SHOW TABLES')->fetchAllColumn();
		foreach ($alltables as $table) {
			CM_Db_Db::delete($table);
		}
		if (CM_File::exists(DIR_TEST_DATA . 'db/data.sql')) {
			CM_Db_Db::runDump(CM_Config::get()->CM_Db_Db->db, new CM_File(DIR_TEST_DATA . 'db/data.sql'));
		}
	}

	public static function clearTmp() {
		CM_App::getInstance()->setupFilesystem();
	}

	public static function clearConfig() {
		CM_Config::set(unserialize(self::$_configBackup));
	}

	public static function timeInit() {
		runkit_function_copy('time', 'time_original');
		runkit_function_redefine('time', '', 'return CMTest_TH::time();');
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
	 * @param string|null $abbreviation
	 * @return CM_Model_Language
	 */
	public static function createLanguage($abbreviation = null) {
		if (!$abbreviation) {
			do {
				$abbreviation = self::_randStr(5);
			} while (CM_Model_Language::findByAbbreviation($abbreviation));
		}
		return CM_Model_Language::create(array('name' => 'English', 'abbreviation' => $abbreviation, 'enabled' => 1));
	}

	/**
	 * @param string             $pageClass
	 * @param CM_Model_User|null $viewer OPTIONAL
	 * @param array              $params OPTIONAL
	 * @return CM_Page_Abstract
	 */
	public static function createPage($pageClass, CM_Model_User $viewer = null, $params = array()) {
		$request = new CM_Request_Get('?' . http_build_query($params), array(), $viewer);
		return new $pageClass(CM_Params::factory($request->getQuery()), $request->getViewer());
	}

	/**
	 * @param CM_Model_User|null $user
	 * @return CM_Session
	 */
	public static function createSession(CM_Model_User $user = null) {
		if (is_null($user)) {
			$user = self::createUser();
		}
		$session = new CM_Session();
		$session->setUser($user);
		$session->write();
		return $session;
	}

	/**
	 * @param int|null $type
	 * @param int|null $adapterType
	 * @return CM_Model_StreamChannel_Abstract
	 */
	public static function createStreamChannel($type = null, $adapterType = null) {
		if (null === $type) {
			$type = CM_Model_StreamChannel_Video::TYPE;
		}

		if (null === $adapterType) {
			$adapterType = CM_Stream_Adapter_Video_Wowza::TYPE;
		}

		$data = array('key' => rand(1, 10000) . '_' . rand(1, 100));
		if (CM_Model_StreamChannel_Video::TYPE == $type) {
			$data['width'] = 480;
			$data['height'] = 720;
			$data['serverId'] = 1;
			$data['thumbnailCount'] = 0;
			$data['adapterType'] = $adapterType;
		}

		return CM_Model_StreamChannel_Abstract::createType($type, $data);
	}

	/**
	 * @param CM_Model_StreamChannel_Video|null $streamChannel
	 * @param CM_Model_User|null                $user
	 * @return CM_Model_StreamChannelArchive_Video
	 */
	public static function createStreamChannelVideoArchive(CM_Model_StreamChannel_Video $streamChannel = null, CM_Model_User $user = null) {
		if (is_null($streamChannel)) {
			$streamChannel = self::createStreamChannel();
			self::createStreamPublish($user, $streamChannel);
		}
		if (!$streamChannel->hasStreamPublish()) {
			self::createStreamPublish($user, $streamChannel);
		}
		return CM_Model_StreamChannelArchive_Video::create(array('streamChannel' => $streamChannel));
	}

	/**
	 * @param CM_Model_User|null                   $user
	 * @param CM_Model_StreamChannel_Abstract|null $streamChannel
	 * @return CM_Model_Stream_Publish
	 */
	public static function createStreamPublish(CM_Model_User $user = null, CM_Model_StreamChannel_Abstract $streamChannel = null) {
		if (!$user) {
			$user = self::createUser();
		}
		if (is_null($streamChannel)) {
			$streamChannel = self::createStreamChannel();
		}
		return CM_Model_Stream_Publish::create(array('streamChannel' => $streamChannel, 'user' => $user, 'start' => time(),
													 'allowedUntil'  => time() + 100, 'key' => rand(1, 10000) . '_' . rand(1, 100)));
	}

	/**
	 * @param CM_Model_User|null                   $user
	 * @param CM_Model_StreamChannel_Abstract|null $streamChannel
	 * @return CM_Model_Stream_Subscribe
	 */
	public static function createStreamSubscribe(CM_Model_User $user = null, CM_Model_StreamChannel_Abstract $streamChannel = null) {
		if (is_null($streamChannel)) {
			$streamChannel = self::createStreamChannel();
		}
		return CM_Model_Stream_Subscribe::create(array('streamChannel' => $streamChannel, 'user' => $user, 'start' => time(),
													   'allowedUntil'  => time() + 100, 'key' => rand(1, 10000) . '_' . rand(1, 100)));
	}

	/**
	 * @param string             $uri
	 * @param array|null         $headers
	 * @param CM_Model_User|null $viewer
	 * @return CM_Response_Page
	 */
	public static function createResponsePage($uri, array $headers = null, CM_Model_User $viewer = null) {
		if (!$headers) {
			$headers = array();
		}
		$request = new CM_Request_Get($uri, $headers, $viewer);
		return new CM_Response_Page($request);
	}

	/**
	 * @return CM_Db_Client
	 */
	public static function getDbClient() {
		if (null !== self::$_dbClient) {
			return self::$_dbClient;
		}
		$config = CM_Config::get()->CM_Db_Db;
		self::$_dbClient = new CM_Db_Client($config->server['host'], $config->server['port'], $config->username, $config->password, $config->db);
		return self::$_dbClient;
	}

	public static function randomizeAutoincrement() {
		$tables = CM_Db_Db::exec('SHOW TABLES')->fetchAllColumn();
		foreach ($tables as $table) {
			if (CM_Db_Db::exec("SHOW COLUMNS FROM `" . $table . "` WHERE `Extra` = 'auto_increment'")->fetch()) {
				CM_Db_Db::exec("ALTER TABLE `" . $table . "` AUTO_INCREMENT = " . rand(1, 1000));
			}
		}
	}

	/**
	 * @param CM_Model_Abstract $model
	 */
	public static function reinstantiateModel(CM_Model_Abstract &$model) {
		$model = CM_Model_Abstract::factoryGeneric($model->getType(), $model->getIdRaw());
	}

	/**
	 * @param int    $length
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
