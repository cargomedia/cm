<?php

class CM_PagingSource_Pagings extends CM_PagingSource_Abstract {

	/**
	 * @var CM_Paging_Abstract[]
	 */
	private $_pagings;

	/**
	 * @var bool
	 */
	private $_distinct;

	/**
	 * @param  CM_Paging_Abstract[] $pagings
	 * @param boolean|null          $distinct
	 * @throws CM_Exception_Invalid
	 */
	public function __construct(array $pagings, $distinct = null) {
		foreach ($pagings as $paging) {
			if (!$paging instanceof CM_Paging_Abstract) {
				throw new CM_Exception_Invalid("Not a Paging.");
			}
		}
		$this->_pagings = $pagings;
		$this->_distinct = (boolean) $distinct;
	}

	public function getCount($offset = null, $count = null) {
		$items = $this->getItems();
		return count($items);
	}

	public function getItems($offset = null, $count = null) {
		$items = array();
		/** @var CM_Paging_Abstract $paging */
		foreach ($this->_pagings as $paging) {
			$items = array_merge($items, $paging->getItemsRaw());
		}
		if ($this->_distinct) {
			$items = array_unique($items, SORT_REGULAR);
		}
		if ($offset || $count) {
			if ($count) {
				$items = array_splice($items, $offset, $count);
			} else {
				$items = array_splice($items, $offset);
			}
		}
		return $items;
	}

	protected function _cacheKeyBase() {
		throw new CM_Exception_Invalid('`' . __CLASS__ . '` does not support caching.');
	}
}
