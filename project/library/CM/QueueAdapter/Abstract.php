<?php

abstract class CM_QueueAdapter_Abstract {
	protected $_key;

	public function __construct($key) {
		$this->_key = $key;
	}

	/**
	 * @param string $key
	 * @param string  $value
	 */
	abstract public function push($key, $value);

	/**
	 * @param string $key
	 * @return string
	 */
	abstract public function pop($key);
}
