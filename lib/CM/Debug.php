<?php

class CM_Debug {
	private static $_instance = null;
	private $_stats = array();
	private $_errors = array();

	/**
	 * Singleton Getter
	 *
	 * @return CM_Debug Depending on 'DEBUG_MODE' a Debug-instance or a DebugDummy
	 */
	public static function get() {
		if (self::$_instance === null) {
			if (DEBUG_MODE) {
				self::$_instance = new self();
			} else {
				self::$_instance = new CM_DebugDummy();
			}
		}
		return self::$_instance;
	}

	/**
	 * adds a new key value pair to the stats array
	 * @param string $key
	 * @param string $value
	 */
	public function incStats($key, $value) {
		if (!array_key_exists($key, $this->_stats)) {
			$this->_stats[$key] = array();
		}
		$this->_stats[$key][] = $value;
	}

	/**
	 * @param string $file
	 * @param int $line
	 * @param string $msg
	 */
	public function addError($file, $line, $msg) {
		$this->_errors[] = array('file' => $file, 'line' => $line, 'msg' => $msg);
	}

	/**
	 * @return array stats array
	 */

	public function getStats() {
		return $this->_stats;
	}

	/**
	 * @return array
	 */
	public function getErrors() {
		return $this->_errors;
	}

}

class CM_DebugDummy {
	public function __call($name, $arguments) {
		return false;
	}
}
