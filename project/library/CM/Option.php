<?php

class CM_Option {
	/**
	 * @var CM_Option
	 */
	private static $_instance;

	/**
	 * @return CM_Option
	 */
	public static function getInstance() {
		if (!self::$_instance) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * @param string $key
	 * @return mixed|null
	 */
	public function get($key) {
		$value = CM_Mysql::select(TBL_CM_OPTION, 'value', array('key' => $key))->fetchOne();
		if (false === $value) {
			return null;
		}
		$value = unserialize($value);
		if (false === $value) {
			throw new CM_Exception_Invalid('Cannot unserialize option `' . $key . '`.');
		}
		return $value;
	}

	/**
	 * @param string $key
	 * @param mixed  $value
	 */
	public function set($key, $value) {
		CM_Mysql::replace(TBL_CM_OPTION, array('key' => $key, 'value' => serialize($value)));
	}
}
