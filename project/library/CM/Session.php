<?php

class CM_Session {

	const ACTIVITY_EXPIRATION = 240; // 4 mins

	/**
	 * @var string
	 */
	private $_id;
	/**
	 * @var array
	 */
	public $_data;
	/**
	 * @var int
	 */
	private $_expires;
	/**
	 * @var CM_Session|null $_instance
	 */
	private static $_instance = null;

	/**
	 * @param string|null $id
	 */
	public function __construct($id = null) {
		if ($id) {
			$this->_id = (string) $id;
			$loadedData = $this->_loadData();
			$data = unserialize($loadedData['data']);
			$expires = (int) $loadedData['expires'];
		} else {
			$id = self::_generateId();
			$data = array();
			$expires = time() + $this->getLifetime();
			$this->_id = (string) $id;
		}
		$this->_data = $data;
		$this->_expires = $expires;
	}

	/**
	 * @param string $key
	 */
	public function delete($key) {
		unset($this->_data[$key]);
	}

	public function deleteUser() {
		if ($this->has('userId')) {
			if ($user = $this->getUser()) {
				$user->setOnline(false);
			}
			$this->delete('userId');
			$this->delete('cookieLifeTime');
			$this->regenerateId();
		}
	}

	/**
	 * @param string $key
	 * @return mixed|null
	 */
	public function get($key) {
		if (isset($this->_data[$key])) {
			return $this->_data[$key];
		}
		return null;
	}

	/**
	 * @param string $key
	 * @param mixed  $value
	 */
	public function set($key, $value) {
		$this->_data[$key] = $value;
	}

	/**
	 * @return int
	 */
	public function getExpiration() {
		return $this->_expires;
	}

	/**
	 * @return string
	 */
	public function getId() {
		return $this->_id;
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

	/**
	 * @param string $key
	 * @return boolean
	 */
	public function has($key) {
		return isset($this->_data[$key]);
	}

	/**
	 * @return bool
	 */
	public function hasLifetime() {
		return $this->has('lifetime');
	}

	public function persist() {
		CM_Mysql::replace(TBL_CM_SESSION, array('sessionId' => $this->getId(), 'data' => serialize($this->_data), 'expires' => $this->_expires));
		$this->_change();
	}

	public function unpersist() {
		CM_Mysql::delete(TBL_CM_SESSION, array('sessionId' => $this->getId()));
		$this->_change();
	}

	public function regenerateId() {
		$this->unpersist();
		$this->_id = self::_generateId();
	}

	public function _change() {
		CM_Cache::delete($this->_getCacheKey());
	}

	private function _getCacheKey() {
		return CM_CacheConst::Session . '_id:' . $this->getId();
	}

	/**
	 * @return array
	 * @throws CM_Exception_Nonexistent
	 */
	private function _loadData() {
		$cacheKey = $this->_getCacheKey();
		if (($data = CM_Cache::get($cacheKey)) === false) {
			$data = CM_Mysql::select(TBL_CM_SESSION, '*', array('sessionId' => $this->getId()))->fetchAssoc();
			if (!$data) {
				throw new CM_Exception_Nonexistent('Session `' . $this->getId() . '` does not exist.');
			}
			CM_Cache::set($cacheKey, $data);
		}
		return $data;
	}

	private function _start() {
		$expiration = $this->getExpiration();
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

	/**
	 * @return CM_Session
	 */
	public static function getInstance($sessionId = null, $renew = null) {
		if (self::$_instance === null || $renew) {
			try {
				self::$_instance = new self($sessionId);
			} catch (CM_Exception_Nonexistent $ex) {
				self::$_instance = new self();
			}
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

	/**
	 * @return string
	 */
	private static function _generateId() {
		$id = md5(uniqid());
		while (CM_Mysql::count(TBL_CM_SESSION, array('sessionId' => $id))) {
			$id = md5(uniqid());
		}
		return $id;
	}
}
