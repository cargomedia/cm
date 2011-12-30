<?php

class CM_SessionHandler {

	private $_data;
	private $_expires;

	private static $_instance;

	public static function register() {
		if (!self::$_instance) {
			self::$_instance = new self();
			session_set_save_handler(
					array(&self::$_instance, 'open'),
					array(&self::$_instance, 'close'),
					array(&self::$_instance, 'read'),
					array(&self::$_instance, 'write'),
					array(&self::$_instance, 'destroy'),
					array(&self::$_instance, 'gc')
			);
		}
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
		if ((list($this->_data, $this->_expires) = CM_Cache::get($cacheKey)) === false) {
			$row = CM_Mysql::exec("SELECT `data`, `expires` FROM TBL_CM_SESSION WHERE `sessionId` = '?' AND `expires` > ?", $id, time())
					->fetchAssoc();
			$this->_data = (string) $row['data'];
			$this->_expires = (int) $row['expires'];
			CM_Cache::set($cacheKey, array($this->_data, $this->_expires));
		}
		return $this->_data;
	}

	public function write($id, $data) {
		$lifetime = 3600;
		if (CM_Session::getInstance()->getViewer()) {
			$lifetime = 14 * 86400;
		}
		$changed = ($data != $this->_data);
		$expiresSoon = ($this->_expires - time() < $lifetime / 2);

		if ($changed || $expiresSoon) {
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
		return true;
	}

	public function gc() {
		CM_Mysql::exec('DELETE FROM TBL_CM_SESSION WHERE `expires` < ?', time());
		return true;
	}
}
