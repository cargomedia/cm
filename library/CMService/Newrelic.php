<?php

class CMService_Newrelic extends CM_Class_Abstract {

	/** @var CMService_Newrelic */
	protected static $_instance;

	/**
	 * @throws CM_Exception_Invalid
	 */
	final public function __construct() {
		if (self::$_instance) {
			throw new CM_Exception_Invalid('Newrelic already instantiated');
		}
		self::$_instance = $this;
	}

	public function setConfig() {
		$name = (string) $this->_getConfig()->appName;
		if ($this->_isEnabled()) {
			newrelic_set_appname($name);
		}
	}

	/**
	 * @param Exception $exception
	 */
	public function setNoticeError(Exception $exception) {
		if ($this->_isEnabled()) {
			newrelic_notice_error($exception->getMessage(), $exception);
		}
	}

	/**
	 * @param string $name
	 */
	public function setNameTransaction($name) {
		$name = (string) $name;
		if ($this->_isEnabled()) {
			newrelic_name_transaction($name);
		}
	}

	/**
	 * @param bool|null $flag
	 */
	public function setBackgroundJob($flag = null) {
		if (null === $flag) {
			$flag = true;
		}
		if ($this->_isEnabled()) {
			newrelic_background_job($flag);;
		}
	}

	/**
	 * @throws CM_Exception_Invalid
	 * @return bool
	 */
	private function _isEnabled() {
		if (self::_getConfig()->enabled) {
			if (!extension_loaded('newrelic')) {
				throw new CM_Exception_Invalid('Newrelic Extension is not installed.');
			}
			return true;
		}
		return false;
	}

	/**
	 * @return CMService_Newrelic
	 * @throws Exception
	 */
	public static function getInstance() {
		if (!self::$_instance) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
}
