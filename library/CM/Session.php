<?php

class CM_Session {

	const ACTIVITY_EXPIRATION = 240; // 4 mins

	/**
	 * @var CM_Session $_instance
	 */
	private static $_instance = null;

	/**
	 * @param string $key
	 * @return misc
	 */
	public function get($key) {
		if (!isset($_SESSION[$key])) {
			return null;
		}
		return $_SESSION[$key];
	}

	/**
	 * @param string $key
	 * @param misc   $data
	 */
	public function set($key, $data) {
		$_SESSION[$key] = $data;
	}

	/**
	 * @param string $key
	 * @return boolean
	 */
	public function has($key) {
		return isset($_SESSION[$key]);
	}

	/**
	 * @param bool $needed OPTIONAL Throw a CM_Exception_AuthRequired if not authenticated
	 * @return CM_Model_User Session-user OR null
	 */
	public function getViewer($needed = false) {
		if ($this->has('userId')) {
			try {
				return CM_Model_User::factory($this->get('userId'));
			} catch (CM_Exception_Nonexistent $ex) {
			}
		}
		if ($needed) {
			throw new CM_Exception_AuthRequired();
		}
		return null;
	}

	public function logout() {
		if ($user = $this->getViewer()) {
			$user->setOnline(false);
		}
		if (session_id()) {
			session_destroy();
			$this->start(true);
		}
	}

	public function login(CM_Model_User $user) {
		$this->start(true);
		$this->set('userId', $user->getId());
	}

	public function start($regenerateId = false) {
		CM_SessionHandler::register();
		if (!headers_sent()) {
			session_start();
			if ($regenerateId) {
				session_regenerate_id(true);
			}
		}

		if (CM_Request_Abstract::isIpBlocked()) {
			$this->logout();
			return;
		}
		if ($user = $this->getViewer()) {
			if (!$user->canLogin()) {
				$this->logout();
				return;
			}
			if ($user->getLatestactivity() < time() - self::ACTIVITY_EXPIRATION / 3) {
				$user->updateLatestactivity();
			}
			if (!$user->getOnline()) {
				$user->setOnline(true);
			}
		}
	}

	/**
	 * @return CM_Session
	 */
	public static function getInstance() {
		if (self::$_instance === null) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public static function logoutOld() {
		$res = CM_Mysql::exec('SELECT `o`.`userId` FROM TBL_CM_USER_ONLINE `o` JOIN TBL_CM_USER `u` USING(`userId`) WHERE `u`.`activityStamp` < ?',
				time() - self::ACTIVITY_EXPIRATION);
		while ($userId = $res->fetchOne()) {
			try {
				$user = CM_Model_User::factory($userId);
				$user->setOnline(false);
			} catch (CM_Exception_Nonexistent $e) {
				CM_Mysql::delete(TBL_CM_USER_ONLINE, array('userId' => $userId));
			}
		}
	}
}
