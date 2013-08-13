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
	 * @throws CM_Exception_Invalid
	 */
	public function __construct($key) {
		$this->_key = (string) $key;
		if (empty($this->_key)) {
			throw new CM_Exception_Invalid('Key is empty');
		}
		$this->_adapter = new CM_QueueAdapter_Redis();
	}

	/**
	 * @return string
	 */
	public function getKey() {
		return $this->_key;
	}

	/**
	 * @param mixed    $value
	 * @param int|null $timestamp
	 */
	public function push($value, $timestamp = null) {
		$timestamp = (null !== $timestamp) ? (int) $timestamp : null;
		$value = serialize($value);
		if (null !== $timestamp) {
			$this->_adapter->pushDelayed($this->getKey(), $value, $timestamp);
		} else {
			$this->_adapter->push($this->getKey(), $value);
		}
	}

	/**
	 * @param int|null $timestampMax
	 * @return mixed
	 */
	public function pop($timestampMax = null) {
		$timestampMax = (null !== $timestampMax) ? (int) $timestampMax : null;
		if (null !== $timestampMax) {
			$result = $this->_adapter->popDelayed($this->_key, $timestampMax);
			$value = array_map(function($value) {
				return unserialize($value);
			}, $result);
		} else {
			$result = $this->_adapter->pop($this->getKey());
			$value = unserialize($result);
		}
		return $value;
	}
}
