<?php
require_once 'TH/Page.php';

/**
 * TH - TestHelper-class with static convenience-methods
 */

class TH {
	private static $db_truncatetables = null;
	private static $timeDelta = 0;
	private static $initialized = false;

	public static function init() {
		if (self::$initialized) {
			return;
		}

		// Setup
		define('DIR_TESTS', dirname(__FILE__) . DIRECTORY_SEPARATOR);
		define('DIR_TEST_DATA', DIR_TESTS . 'data' . DIRECTORY_SEPARATOR);
		define('IS_TEST', true);

		define('DIR_ROOT', dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR);
		require_once DIR_ROOT . 'library/CM/Bootloader.php';
		CM_Bootloader::load(array('Autoloader', 'constants', 'exceptionHandler', 'errorHandler', 'defaults'));

		// Create db
		if (count(self::_runSql("SHOW DATABASES LIKE '" . CM_Config::get()->CM_Mysql->db . "_test'")) == 0) {
			echo 'Exporting `skadate`...';
			self::_runCmd(DIR_TEST_DATA . 'db/export.sh');
			echo PHP_EOL;
			echo 'Importing `skadate_test`...';
			self::_runSql("CREATE DATABASE " . CM_Config::get()->CM_Mysql->db . "_test");
			self::_loadDb(DIR_TEST_DATA . 'db/dump.sql', CM_Config::get()->CM_Mysql->db . '_test');
			echo PHP_EOL;
		}

		// Reset environment
		self::clearEnv();
		self::timeInit();

		self::$initialized = true;
	}

	/**
	 * @param SK_Entity_Profile $profile
	 * @return SK_Entity_Profile
	 */
	public static function login(SK_Entity_Profile $profile = null) {
		if (!$profile) {
			$profile = self::createProfile();
		}
		CM_Session::getInstance()->setUser($profile->getUser());
		return $profile;
	}

	/**
	 * @param SK_Entity_Profile $profile
	 * @return SK_Entity_Photo
	 */
	public static function createPhoto(SK_Entity_Profile $profile = null) {
		// TODO Add real image to also test rotate ...
		if (!$profile) {
			$profile = self::createProfile();
		}
		$photoId = CM_Mysql::exec("INSERT INTO TBL_PROFILE_PHOTO (`profile_id`, `createStamp`, `privacy`)
			VALUES (?, ?, ?)", $profile->getId(), time(), SK_ModelAsset_Entity_Privacy::NONE);
		$photo = new SK_Entity_Photo($photoId);
		$profile->getPhotos()->_change();
		return $photo;
	}

	/**
	 * @return CM_Model_User
	 */
	public static function createUser() {
		return CM_Model_User::create();
	}

	/**
	 * @return CM_Model_User
	 */
	public static function createUserPremium() {
		$user = self::createUser();
		$user->getRoles()->add(SK_Role::PREMIUM, 1000 * 86400);
		return $user;
	}

	/**
	 * @param SK_Entity_Profile $profile
	 * @return SK_Entity_Video
	 */
	public static function createVideo(SK_Entity_Profile $profile = null) {
		if (!$profile) {
			$profile = self::createProfile();
		}
		$embed = array('type' => 'iframe', 'src' => 'http://www.youtube.com/embed/MtN1YnoL46Q', 'ratio' => 0.61);
		return $profile->getVideos()->add(array('title' => 'Duck song', 'privacy' => SK_ModelAsset_Entity_Privacy::NONE, 'embed' => $embed));
	}

	/**
	 * @param SK_Entity_Profile $profile
	 * @return SK_Entity_Blogpost
	 */
	public static function createBlogpost(SK_Entity_Profile $profile = null) {
		if (!$profile) {
			$profile = self::createProfile();
		}
		return $profile->getBlogposts()->add(array('title' => 'TestPost', 'text' => 'TestText'));
	}

	/**
	 * @param SK_Entity_Profile $sender	OPTIONAL
	 * @param SK_Entity_Profile $recipient OPTIONAL
	 * @return SK_Entity_Conversation
	 */
	public static function createConversation(SK_Entity_Profile $sender = null, SK_Entity_Profile $recipient = null) {
		if (!$sender) {
			$sender = self::createProfile();
		}
		if (!$recipient) {
			$recipient = self::createProfile();
		}
		$conversation = $sender->getConversations()->add('subject' . rand(), $recipient);
		$conversation->getMessages()->addText($sender, 'some random text blah blah blah!');
		return $conversation;
	}

	/**
	 * @param SK_Entity_Profile $profile OPTIONAL
	 * @return SK_Entity_Status
	 */
	public static function createStatus(SK_Entity_Profile $profile = null) {
		if (!$profile) {
			$profile = self::createProfile();
		}
		return $profile->getStatuses()->add('Hello there!');
	}

	public static function clearEnv() {
		self::clearCache();
		self::clearDb();
		self::timeReset();
	}

	public static function clearCache() {
		CM_Cache::flush();
		CM_CacheLocal::flush();
	}

	public static function clearDb() {
		if (self::$db_truncatetables === null) {
			$keeptables = file(DIR_TEST_DATA . 'db/keeptables.txt', FILE_IGNORE_NEW_LINES);
			$alltables = CM_Mysql::query('SHOW TABLES')->fetchCol();
			self::$db_truncatetables = array_diff($alltables, $keeptables);
		}
		foreach (self::$db_truncatetables as $table) {
			self::truncateTable($table);
		}
	}

	public static function truncateTable($table) {
		CM_Mysql::query('TRUNCATE TABLE `' . $table . '`');
	}

	private static function _runCmd($cmd) {
		exec($cmd, $output, $return_status);
		if ($return_status != 0) {
			exit(1);
		}
		return $output;
	}

	private static function _runSql($sql, $dbName = null) {
		$cmd = 'mysql -u' . CM_Config::get()->CM_Mysql->user . ' -p' . CM_Config::get()->CM_Mysql->pass . ' -h' . CM_Config::get()->CM_Mysql->server['host'] .
				' -P ' . CM_Config::get()->CM_Mysql->server['port'];
		if ($dbName) {
			$cmd .= ' ' . $dbName;
		}
		$cmd .= ' -s -e"' . $sql . '"';
		return self::_runCmd($cmd);
	}

	private static function _loadDb($sqlFile, $dbName) {
		$cmd = 'mysql -u' . CM_Config::get()->CM_Mysql->user . ' -p' . CM_Config::get()->CM_Mysql->pass . ' -h' . CM_Config::get()->CM_Mysql->server['host'] .
				' -P ' . CM_Config::get()->CM_Mysql->server['port'] . ' ' . $dbName . ' < ' . $sqlFile;
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
	 * @param SK_Entity_Profile		   $profile
	 * @param SK_PaymentProvider_Abstract $paymentProvider
	 * @param SK_ServiceBundle			$serviceBundle
	 *
	 * @return string
	 */
	public static function payInitial(SK_Entity_Profile $profile, SK_PaymentProvider_Abstract $paymentProvider, SK_ServiceBundle $serviceBundle) {
		$key = md5(rand());
		$randomHash = md5(rand() . uniqid());
		$username = substr($randomHash, 0, 16);
		$password = substr($randomHash, 16);
		$providerBundleId = $paymentProvider->getProviderBundleId($serviceBundle);

		switch ($paymentProvider->getId()) {
			case 4:
				$query = http_build_query(array('typeId' => $providerBundleId, 'clientAccnum' => $paymentProvider->getAccountNumber(),
					'clientSubacc' => $paymentProvider->getSubAccount(), 'username' => $username, 'password' => $password, 'subscription_id' => $key,
					'initialPrice' => $serviceBundle->getPrice(), 'reasonForDeclineCode' => '', 'profileId' => $profile->getUserId()));
				$uri = '/payment/ccbill/' . $query;
				$request = new CM_Request_Post($uri, array(), $query, CM_Request_Post::FORMAT_FORM);
				$response = new SK_Response_Checkout_CCBill($request);
				$response->process();
				break;
			case 100:
				$query = http_build_query(array('PRICING_ID' => $providerBundleId, 'ZombaioGWPass' => $paymentProvider->getZombaioPass(),
					'Action' => 'user.add', 'SUBSCRIPTION_ID' => $key, 'TRANSACTION_ID' => md5(rand()), 'SITE_ID' => $paymentProvider->getSiteId(),
					'username' => $username, 'password' => $password, 'Amount' => $serviceBundle->getPrice(), 'extra' => $profile->getUserId()));
				$uri = '/payment/zombaio/?' . $query;
				$request = new CM_Request_Get($uri, array());
				$response = new SK_Response_Checkout_Zombaio($request);
				$response->process();
				break;
		}
		return $key;
	}

	/**
	 * @param int	$providerId
	 * @param string $subscriptionKey
	 * @param float  $amount
	 *
	 * @return string
	 */
	public static function payRebill(SK_PaymentProvider_Abstract $paymentProvider, $subscriptionKey, $amount) {
		$key = md5(rand());
		switch ($paymentProvider->getId()) {
			case 4:
				$data = array('REBILL', $paymentProvider->getAccountNumber(), $paymentProvider->getSubAccount(), $subscriptionKey, date("Y-m-d"),
					$key, // transactionKey
					$amount, // Amount
				);
				$GLOBALS['TEST_CCBILL_DATALINK'] = '"' . implode('","', $data) . '"';
				$paymentProvider->cronCheckout(true);
				break;
			case 100:
				$query = http_build_query(array('ZombaioGWPass' => $paymentProvider->getZombaioPass(), 'Action' => 'rebill',
					'SUBSCRIPTION_ID' => $subscriptionKey, 'TRANSACTION_ID' => $key, 'SiteID' => $paymentProvider->getSiteId(), 'Success' => 1,
					'Amount' => $amount));
				$uri = '/payment/zombaio/?' . $query;
				$request = new CM_Request_Get($uri, array());
				$response = new SK_Response_Checkout_Zombaio($request);
				$response->process();
				break;
		}
		return $key;
	}

	public static function inProfileArray(SK_Entity_Profile $needle, array $haystack) {
		foreach ($haystack as $straw) {
			if ($needle->equals($straw)) {
				return true;
			}
		}
		return false;
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
