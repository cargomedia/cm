<?php

class CM_Queue {
	/**
	 * @var string
	 */
	private $_key;

	/**
	 * @var CM_QueueAdapter_Abstract
	 */
	private $_adapter;

	/**
	 * @param string $key
	 */
	public function __construct($key) {
		$this->_key = (string) $key;
		if (empty($this->_key)) {
			throw new CM_Exception_Invalid('Key is empty');
		}
		$this->_adapter = new CM_QueueAdapter_Redis($key);
	}

	/**
	 * @return string
	 */
	public function getKey() {
		return $this->_key;
	}

	/**
	 * @param mixed $value
	 */
	public function push($value) {
		$value = serialize($value);
		$this->_adapter->push($this->getKey(), $value);
	}

	/**
	 * @return mixed
	 */
	public function pop() {
		$value = $this->_adapter->pop($this->getKey());
		$value = unserialize($value);
		return $value;
	}
}
