<?php

class CM_Session {

	const ACTIVITY_EXPIRATION = 240; // 4 mins

	/**
	 * @var CM_Session $_instance
	 */
	private static $_instance = null;

	private function _start() {
		if (!headers_sent()) {
			session_start();
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
	 * @param string $key
	 * @return mixed
	 */
	public function get($key) {
		if (!isset($_SESSION[$key])) {
			return null;
		}
		return $_SESSION[$key];
	}

	/**
	 * @param string  $key
	 * @param mixed   $data
	 */
	public function set($key, $data) {
		$_SESSION[$key] = $data;
	}

	/**
	 * @param string $key
	 */
	public function delete($key) {
		unset($_SESSION[$key]);
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
		$this->delete('userId');
		$this->regenerateId();
	}

	/**
	 * @param CM_Model_User $user
	 * @param int|null $cookieLifetime
	 */
	public function login(CM_Model_User $user, $cookieLifetime = null) {
		if ($cookieLifetime) {
			session_set_cookie_params($cookieLifetime);
		}
		$this->regenerateId();
		$this->set('userId', $user->getId());
		$user->setOnline(true);
	}

	public function regenerateId() {
		if (!headers_sent()) {
			session_regenerate_id(true);
		}
	}

	/**
	 * @return CM_Session
	 * @param boolean $renew OPTIONAL
	 */
	public static function getInstance($renew = false) {
		if (self::$_instance === null || $renew) {
			self::$_instance = new self();
			CM_SessionHandler::register();
			self::$_instance->_start();
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
