<?php

class CM_Set {
	/**
	 * @var string
	 */
	private $_key;

	/**
	 * @var CM_SetAdapter_Abstract
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
		$this->_adapter = new CM_SetAdapter_Redis($key);
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
	public function add($value) {
		$value = serialize($value);
		$this->_adapter->add($this->getKey(), $value);
	}

	/**
	 * @param mixed $value
	 */
	public function delete($value) {
		$value = serialize($value);
		$this->_adapter->delete($this->getKey(), $value);
	}

	/**
	 * @return mixed[]
	 */
	public function flush() {
		$values = $this->_adapter->flush($this->getKey());
		foreach ($values as &$value) {
			$value = unserialize($value);
		}
		return $values;
	}

}
