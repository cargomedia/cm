<?php

class CM_Session implements CM_Comparable {

	const ACTIVITY_EXPIRATION = 240; // 4 mins
	const LIFETIME_DEFAULT = 3600;

	/** @var string */
	private $_id;

	/** @var array */
	private $_data;

	/** @var int */
	private $_expires;

	/** @var boolean */
	private $_write = false;

	/** @var boolean */
	private $_isPersistent = false;

	/**
	 * @param string|null $id
	 * @throws CM_Exception_Nonexistent
	 */
	public function __construct($id = null) {
		if (null !== $id) {
			$this->_id = (string) $id;
			$data = self::_findDataById($this->getId());
			if (null === $data) {
				throw new CM_Exception_Nonexistent('Session `' . $this->getId() . '` does not exist.');
			}
			$this->_isPersistent = true;
			$expires = (int) $data['expires'];
			$data = unserialize($data['data']);
		} else {
			$id = self::_generateId();
			$data = array();
			$expires = time() + $this->getLifetime();
			$this->_id = (string) $id;
			$this->_write = true;
		}
		$this->_data = $data;
		$this->_expires = $expires;
	}

	public function __destruct() {
		if ($this->_write) {
			$this->write();
		}
	}

	/**
	 * @param string $key
	 */
	public function delete($key) {
		unset($this->_data[$key]);
		$this->_write = true;
	}

	public function deleteUser() {
		if ($this->has('userId')) {
			if ($user = $this->getUser()) {
				$user->setOnline(false);
			}
			$this->delete('userId');
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
		$this->_write = true;
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
			return self::LIFETIME_DEFAULT;
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
	 * @param bool|null $needed
	 * @throws CM_Exception_AuthRequired
	 * @return CM_Model_User|null
	 */
	public function getUser($needed = null) {
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
	 * @return boolean
	 */
	public function hasLifetime() {
		return $this->has('lifetime');
	}

	/**
	 * @return boolean
	 */
	public function hasUser() {
		return $this->has('userId');
	}

	/**
	 * @return boolean
	 */
	public function isEmpty() {
		return empty($this->_data);
	}

	public function regenerateId() {
		$newId = self::_generateId();
		if ($this->_isPersistent) {
			CM_Db_Db::update(TBL_CM_SESSION, array('sessionId' => $newId), array('sessionId' => $this->getId()));
			$this->_change();
		}
		$this->_id = $newId;
	}

	public function start() {
		$expiration = $this->getExpiration();
		$expiresSoon = ($expiration - time() < $this->getLifetime() / 2);
		if ($expiresSoon) {
			$this->_write = true;
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

	public function write() {
		if (!$this->isEmpty()) {
			CM_Mysql::replace(TBL_CM_SESSION, array('sessionId' => $this->getId(),
													'data'      => serialize($this->_data),
													'expires'   => time() + $this->getLifetime()));
			$this->_change();
		} elseif ($this->_isPersistent) {
			CM_Mysql::delete(TBL_CM_SESSION, array('sessionId' => $this->getId()));
			$this->_change();
		}
	}

	/**
	 * @param CM_Comparable $other
	 * @return boolean
	 */
	public function equals(CM_Comparable $other = null) {
		if (empty($other)) {
			return false;
		}
		/** @var CM_Session $other */
		return (get_class($this) === get_class($other) && $this->getId() === $other->getId());
	}

	private function _change() {
		if ($this->_isPersistent) {
			CM_Cache::delete(self::_getCacheKey($this->getId()));
		}
	}

	/**
	 * @param string $id
	 * @return CM_Session|null
	 */
	public static function findById($id) {
		if (null === self::_findDataById($id)) {
			return null;
		}
		return new self($id);
	}

	public static function deleteExpired() {
		CM_Db_Db::delete(TBL_CM_SESSION, '`expires` < ' . time());
	}

	/**
	 * @param string $id
	 * @return array|null
	 */
	private static function _findDataById($id) {
		$cacheKey = self::_getCacheKey($id);
		if (($data = CM_Cache::get($cacheKey)) === false) {
			$data = CM_Mysql::select(TBL_CM_SESSION, array('data', 'expires'), array('sessionId' => $id))->fetchAssoc();
			if (!$data) {
				return null;
			}
			CM_Cache::set($cacheKey, $data, self::LIFETIME_DEFAULT);
		}
		return $data;
	}

	/**
	 * @param string $id
	 * @return string
	 */
	private static function _getCacheKey($id) {
		return CM_CacheConst::Session . '_id:' . $id;
	}

	/**
	 * @return string
	 */
	private static function _generateId() {
		return md5(rand() . uniqid());
	}
}
