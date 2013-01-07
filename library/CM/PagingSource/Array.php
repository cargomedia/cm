<?php

class CM_PagingSource_Array extends CM_PagingSource_Abstract {

	/**
	 * @var array
	 */
	private $_data;

	/**
	 * @param array $data
	 */
	public function __construct(array $data) {
		$this->_data = $data;
	}

	public function getCount($offset = null, $count = null) {
		return count($this->_data);
	}

	public function getItems($offset = null, $count = null) {
		return array_slice($this->_data, $offset, $count);
	}

	protected function _cacheKeyBase() {
		throw new CM_Exception_Invalid('`' . __CLASS__ . '` does not support caching.');
	}

}
