<?php

class CMTest_TH {

	private static $timeDelta = 0;
	private static $initialized = false;
	private static $_configBackup;

	public static function init() {
		if (self::$initialized) {
			return;
		}
		$dbName = CM_Config::get()->CM_Mysql->db;
		if (CM_Config::get()->CMTest_TH->dropDatabase) {
			try {
				CM_Mysql::exec('DROP DATABASE IF EXISTS `' . $dbName . '`');
			} catch (CM_Mysql_DbSelectException $e) {
				// Database does not exist
			}
		}

		try {
			CM_Mysql::selectDb($dbName);
		} catch (CM_Mysql_DbSelectException $e) {
			CM_Mysql::exec('CREATE DATABASE `' . $dbName . '`');

			CM_Mysql::selectDb($dbName);
			foreach (CM_Util::getResourceFiles('db/structure.sql') as $dump) {
				CM_Mysql::runDump($dbName, $dump);
			}
		}

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
		$alltables = CM_Mysql::query('SHOW TABLES')->fetchCol();
		foreach ($alltables as $table) {
			CM_Db_Db::truncate($table);
		}
		if (CM_File::exists(DIR_TEST_DATA . 'db/data.sql')) {
			CM_Mysql::runDump(CM_Config::get()->CM_Mysql->db, new CM_File(DIR_TEST_DATA . 'db/data.sql'));
		}
	}

	public static function clearTmp() {
		CM_Util::rmDirContents(DIR_TMP);
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
			'allowedUntil' => time() + 100, 'key' => rand(1, 10000) . '_' . rand(1, 100)));
	}

	/**
	 * @param CM_Model_User|null                         $user
	 * @param CM_Model_StreamChannel_Abstract|null       $streamChannel
	 * @return CM_Model_Stream_Subscribe
	 */
	public static function createStreamSubscribe(CM_Model_User $user = null, CM_Model_StreamChannel_Abstract $streamChannel = null) {
		if (is_null($streamChannel)) {
			$streamChannel = self::createStreamChannel();
		}
		return CM_Model_Stream_Subscribe::create(array('streamChannel' => $streamChannel, 'user' => $user, 'start' => time(),
			'allowedUntil' => time() + 100, 'key' => rand(1, 10000) . '_' . rand(1, 100)));
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

	public static function randomizeAutoincrement() {
		$tables = CM_Mysql::query('SHOW TABLES')->fetchCol();
		foreach ($tables as $table) {
			if (CM_Mysql::exec("SHOW COLUMNS FROM `?` WHERE `Extra` = 'auto_increment'", $table)->numRows() > 0) {
				CM_Mysql::exec("ALTER TABLE `?` AUTO_INCREMENT = ?", $table, rand(1, 1000));
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
