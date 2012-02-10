<?php

class CM_PagingSource_Pagings extends CM_PagingSource_Abstract {

	private $_pagings = array();
	private $_unique;

	/**
	 * @param CM_Paging_Abstract[]
	 * @param boolean|null $unique
	 */
	public function __construct(array $pagings, $unique = null) {
		foreach ($pagings as $paging) {
			if (!$paging instanceof CM_Paging_Abstract) {
				throw new CM_Exception_Invalid("Not a Paging.");
			}
		}
		$this->_unique = (boolean) $unique;
		$this->_pagings = $pagings;
	}

	public function enableCache($lifetime = 600) {
		throw new CM_Exception_Invalid('Caching is strictly forbidden!!!');
	}

	public function enableCacheLocal($lifetime = 60) {
		throw new CM_Exception_Invalid('Caching is strictly forbidden!!!');
	}

	/**
	 * @param int $offset
	 * @param int $count
	 * @return int
	 */
	public function getCount($offset = null, $count = null) {
		if ($this->_unique || $offset) {
			$items = $this->getItems($offset, $count);
			$count = count($items);
		} else {
			$count = 0;
			/** @var CM_Paging_Abstract $paging */
			foreach ($this->_pagings as $paging) {
				$count += $paging->getCount();
			}
		}
		return $count;
	}

	/**
	 * @param int $offset
	 * @param int $count
	 * @return array
	 */
	public function getItems($offset = null, $count = null) {
		$items = array();
		/** @var CM_Paging_Abstract $paging */
		foreach ($this->_pagings as $paging) {
			if ($this->_unique) {
				foreach ($paging->getItemsRaw() as $item) {
					if (!in_array($item, $items)) {
						$items[] = $item;
					}
				}
			} else {
				$items = array_merge($items, $paging->getItemsRaw());
			}
		}
		if ($offset) {
			if ($count) {
				$items = array_splice($items, $offset, $count);
			} else {
				$items = array_splice($items, $offset);
			}
		}
		return $items;
	}

	/**
	 * @return mixed
	 */
	protected function _cacheKeyBase() {
	}
}
