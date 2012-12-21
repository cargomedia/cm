<?php

class CM_PagingSource_PagingGroup extends CM_PagingSource_Abstract {

	/** @var CM_Paging_Abstract */
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
		$this->_setPage($offset, $count);
		return $this->_paging->getCount();
	}

	public function getItems($offset = null, $count = null) {
		$this->_setPage($offset, $count);
		$groupedItems = array();
		$getGroupKey = $this->_getGroupKey;
		foreach ($this->_paging->getItems() as $item) {
			$groupedItems[$getGroupKey($item)][] = $item;
		}
		return array_values($groupedItems);
	}

	public function getStalenessChance() {
		return 0.1;
	}

	protected function _cacheKeyBase() {
		throw new CM_Exception_Invalid('`' . __CLASS__ . '` does not support caching.');
	}

	/**
	 * @param int|null $offset
	 * @param int|null $count
	 */
	private function _setPage($offset = null, $count = null) {
		if (null !== $offset && null !== $count) {
			$this->_paging->setPage(ceil($offset / $count) + 1, $count);
		}
	}
}
