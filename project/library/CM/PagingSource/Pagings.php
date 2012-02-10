<?php

class CM_PagingSource_Pagings extends CM_PagingSource_Abstract {

	private $_pagings = array();
	private $_unique;
	private $_field;

	/**
	 * @param CM_Paging_Abstract[]
	 * @param boolean|null $unique
	 */
	public function __construct(array $pagings, $field = null, $unique = null) {
		foreach ($pagings as $paging) {
			if (!$paging instanceof CM_Paging_Abstract) {
				throw new CM_Exception_Invalid("Not a Paging.");
			}
		}
		$this->_pagings = $pagings;
		$this->_field = (string) $field;
		$this->_unique = (boolean) $unique;
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
		if ($this->_unique || $this->_field || $offset || $count) {
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
			if ($this->_unique || $this->_field) {
				foreach ($paging->getItemsRaw() as $item) {
					if ($this->_field && is_array($item)) {
						if (isset($item[$this->_field])) {
							$item = $item[$this->_field];
						} else {
							$item = null;
						}
					}
					if ($item && (!$this->_unique || !in_array($item, $items))) {
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
