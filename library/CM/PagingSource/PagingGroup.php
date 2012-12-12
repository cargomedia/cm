<?php

class CM_PagingSource_PagingGroup extends CM_PagingSource_Abstract {

	/** @var array */
	private $_groupedItems;

	/**    @var CM_Paging_Abstract */
	private $_paging;

	/** @var Closure */
	private $_getGroupKey;

	/**
	 * @param CM_Paging_Abstract $paging
	 * @param Closure            $getGroupKey
	 */
	public function __construct(CM_Paging_Abstract $paging, Closure $getGroupKey) {
		$this->_paging = $paging;
		$this->_getGroupKey = $getGroupKey;
	}

	public function getCount($offset = null, $count = null) {
		return count($this->_getGroupedItems());
	}

	public function getItems($offset = null, $count = null) {
		return array_slice($this->_getGroupedItems(), $offset, $count);
	}

	protected function _cacheKeyBase() {
		throw new CM_Exception_Invalid('`' . __CLASS__ . '` does not support caching.');
	}

	/**
	 * return array
	 */
	private function _getGroupedItems() {
		if (null === $this->_groupedItems) {
			$this->_groupedItems = array();
			$getGroupKey = $this->_getGroupKey;
			foreach ($this->_paging as $item) {
				$this->_groupedItems[$getGroupKey($item)][] = $item;
			}
		}
		return $this->_groupedItems;
	}
}
