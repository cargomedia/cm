<?php

class CM_PagingSource_PagingGroup extends CM_PagingSource_Abstract {

	/**
	 * @var array
	 */
	private $_data;

	/**
	 * @param array $paging
	 */
	public function __construct(CM_Paging_Abstract $paging, Closure $getGroupKey) {
		$this->_data = array();
		foreach ($paging as $item) {
			$this->_data[$getGroupKey($item)][] = $item;
		}
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
