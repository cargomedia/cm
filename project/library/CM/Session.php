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

		$this->_applyCookieLifetime();

		$expiration = CM_SessionHandler::getInstance()->getExpiration($this->getId());
		$expiresSoon = ($expiration - time() < $this->getLifetime() / 2);
		if ($expiresSoon) {
			$this->regenerateId();
		}

		if ($user = $this->getUser()) {
			if (!$user->canLogin()) {
				$this->deleteUser();
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

	private function _applyCookieLifetime() {
		if ($this->hasLifetime()) {
			session_set_cookie_params($this->getLifetime());
		} else {
			session_set_cookie_params(0);
		}
	}

	/**
	 * @return string|false
	 */
	public function getId() {
		$id = session_id();
		if (empty($id)) {
			return false;
		}
		return $id;
	}

	/**
	 * @param string $key
	 * @return mixed|null
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
	 * @return int
	 */
	public function getLifetime() {
		if (!$this->hasLifetime()) {
			return 3600;
		}
		return (int) $this->get('lifetime');
	}

	/**
	 * @param int|null $lifetime
	 */
	public function setLifetime($lifetime = null) {
		$lifetime = (int) $lifetime;
		if ($lifetime) {
			$this->set('lifetime', $lifetime);
		} else {
			$this->delete('lifetime');
		}
		$this->_applyCookieLifetime();
	}

	/**
	 * @return bool
	 */
	public function hasLifetime() {
		return $this->has('lifetime');
	}

	/**
	 * @param bool $needed OPTIONAL Throw a CM_Exception_AuthRequired if not authenticated
	 * @return CM_Model_User Session-user OR null
	 */
	public function getUser($needed = false) {
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

	/**
	 * @param CM_Model_User $user
	 */
	public function setUser(CM_Model_User $user) {
		$user->setOnline(true);
		$this->set('userId', $user->getId());
		$this->regenerateId();
	}

	public function deleteUser() {
		if ($user = $this->getUser()) {
			$user->setOnline(false);
		}
		$this->delete('userId');
		$this->setLifetime(null);
		$this->regenerateId();
	}

	public function regenerateId() {
		if (!headers_sent()) {
			session_regenerate_id(true);
		}
	}

	/**
	 * @param string $sessionId
	 * @return array
	 */
	public static function getData($sessionId) {
		$encodedData = CM_SessionHandler::getInstance()->read(((string) $sessionId));
		if (!$encodedData) {
			throw new CM_Exception_Invalid('Session `' . $sessionId . "` has no data or doesn't exist.");
		}
		$decodedData = array();
		$vars = preg_split('/([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\|/', $encodedData, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
		for ($i = 0; $vars[$i]; $i++) {
			$decodedData[$vars[$i++]] = unserialize($vars[$i]);
		}
		return $decodedData;
	}

	/**
	 * @param boolean $renew OPTIONAL
	 * @return CM_Session
	 */
	public static function getInstance($renew = false) {
		if (self::$_instance === null || $renew) {
			self::$_instance = new self();
			CM_SessionHandler::getInstance();
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
