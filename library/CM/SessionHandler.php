<?php

class CM_SessionHandler {
	private $_data;
	private $_expiration;

	private static $_instance;

	private function __construct() {
		session_set_save_handler(array($this, 'open'), array($this, 'close'), array($this, 'read'), array($this, 'write'), array($this,
			'destroy'), array($this, 'gc'));
	}

	/**
	 * @return CM_SessionHandler
	 */
	public static function getInstance() {
		if (!self::$_instance) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function __destruct() {
		session_write_close();
	}

	public function open($savePath, $sessionName) {
		return true;
	}

	public function close() {
		return true;
	}

	public function read($id) {
		$cacheKey = CM_CacheConst::Session . '_id:' . $id;
		if ((list($this->_data, $this->_expiration) = CM_Cache::get($cacheKey)) === false) {
			$row = CM_Mysql::exec("SELECT `data`, `expires` FROM TBL_CM_SESSION WHERE `sessionId` = '?' AND `expires` > ?", $id, time())->fetchAssoc();
			$this->_data = (string) $row['data'];
			$this->_expiration = (int) $row['expires'];
			CM_Cache::set($cacheKey, array($this->_data, $this->_expiration));
		}
		return $this->_data;
	}

	public function write($id, $data) {
		$lifetime = CM_Session::getInstance()->getLifetime();

		if ($data !== $this->_data) {
			CM_Mysql::replace(TBL_CM_SESSION, array('sessionId' => $id, 'data' => $data, 'expires' => time() + $lifetime));
			$cacheKey = CM_CacheConst::Session . '_id:' . $id;
			CM_Cache::delete($cacheKey);
		}
		return true;
	}

	public function destroy($id) {
		CM_Mysql::delete(TBL_CM_SESSION, array('sessionId' => $id));
		$cacheKey = CM_CacheConst::Session . '_id:' . $id;
		CM_Cache::delete($cacheKey);
		$this->_data = $this->_expiration = null;
		return true;
	}

	public function gc() {
		CM_Mysql::exec('DELETE FROM TBL_CM_SESSION WHERE `expires` < ?', time());
		return true;
	}

	/**
	 * @param string $id
	 * @return int
	 */
	public function getExpiration($id) {
		$this->read($id);
		return $this->_expiration;
	}
}
