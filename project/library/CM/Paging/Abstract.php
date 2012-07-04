<?php

abstract class CM_Paging_Abstract extends CM_Class_Abstract implements Iterator, CM_Cacheable {
	private $_count = null;
	private $_itemsRaw = null, $_items = null;
	private $_pageOffset = 0;
	private $_pageSize = null;
	private $_source = null;
	private $_itemsPosition = 0;
	private $_filters = array();

	/**
	 * @param CM_PagingSource_Abstract $source
	 */
	public function __construct(CM_PagingSource_Abstract $source = null) {
		$this->_source = $source;
	}

	/**
	 * @param int|null  $offset Negative: from end
	 * @param int|null  $length
	 * @param bool|null $returnNonexistentItems
	 * @return array
	 */
	public function getItems($offset = null, $length = null, $returnNonexistentItems = false) {
		$negativeOffset = false;
		$itemsRaw = $this->_getItemsRaw();
		$forceFill = true; //whether the method should fill the array if it hasn't enough values

		// Count of available items
		$count = count($itemsRaw);
		if (null !== $this->_pageSize) {
			$count = min($count, $this->_pageSize);
		}

		// Offset
		if (null === $offset) {
			$offset = 0;
		}
		if ($offset < 0) {
			$negativeOffset = true;
			$offset = $count - (-$offset);
		}
		$offset = max(0, $offset);
		if ($offset >= $count) {
			$length = 0;
		}
		// Length
		if (null === $length) {
			$length = $count - $offset;
			$forceFill = $negativeOffset;
		} else {
			if ($negativeOffset) {
				$length = min($length, $count - $offset);
			}
		}
		$length = max(0, min($count, $length));

		$nonExistentItemsExpected = ($this->_getStalenessChance() != 0);
		$nonProcessableItemsExpected = $nonExistentItemsExpected || count($this->_filters);

		if ($nonProcessableItemsExpected) {
			// Process all items, exclude or null non-processable ones
			if ($this->_items === null) {
				$this->_items = array();
			}
			$direction = 1;
			$items = array();
			$i = 0;
			while (count($items) < $length) {
				$item = null;
				$index = $offset + ($i * $direction);
				if (array_key_exists($index, $this->_items)) {
					$item = $this->_items[$index];
				} else {
					try {
						$item = $this->_processItem($itemsRaw[$index]);
					} catch (CM_Exception_Nonexistent $e) {
					}
				}
				if (!is_null($item) || $returnNonexistentItems) {
					if (is_null($item) || !$this->_isFilterMatch($item)) {
						$items[$index] = $item;
						$this->_items[$index] = $item;
					}
				}
				if ($index == count($itemsRaw) - 1) {
					if (!$forceFill) {
						break;
					}
					$i = 0;
					$direction = -1;
				}
				$i++;
			}
			ksort($items);
			ksort($this->_items);
		} else {
			// Fill all items with null, process the ones which are needed
			if ($this->_items === null) {
				$this->_items = $count ? array_fill(0, $count, null) : array();
			}
			for ($i = $offset; $i < $offset + $length; $i++) {
				if (!isset($this->_items[$i])) {
					$this->_items[$i] = $this->_processItem($itemsRaw[$i]);
				}
			}
			$items = $this->_items;
			if ($offset > 0 || $length < $count) {
				array_slice(items, $offset, $length);
			}
		}
		return array_values($items);
	}

	/**
	 * Return Un-processed, un-filtered items
	 *
	 * @return array
	 */
	public function getItemsRaw() {
		$itemsRaw = $this->_getItemsRaw();
		if (null !== $this->_pageSize && count($itemsRaw) > $this->_pageSize) {
			$itemsRaw = array_slice($itemsRaw, 0, $this->_pageSize);
		}
		return $itemsRaw;
	}

	/**
	 * @param int $offset Negative: from end
	 * @return mixed|null Item at given index
	 */
	public function getItem($offset) {
		$items = $this->getItems($offset, 1);
		return array_shift($items);
	}

	/**
	 * @return mixed|null
	 */
	public function getItemRand() {
		$offset = rand(0, $this->getCount() - 1);
		return $this->getItem($offset);
	}

	/**
	 * @return int
	 */
	public function getCount() {
		if ($this->_count === null && $this->_source) {
			$this->_setCount($this->_source->getCount($this->_getItemOffset(), ceil($this->_pageSize * $this->_getPageFillRate())));
		}
		return (int) $this->_count;
	}

	/**
	 * @return int
	 */
	public function getPage() {
		return ($this->_pageOffset + 1);
	}

	/**
	 * @param int $page
	 * @param int $size
	 * @return CM_Paging_Abstract
	 */
	public function setPage($page, $size) {
		$this->_clearItems();
		$this->_pageOffset = max(((int) $page - 1), 0);
		$this->_pageSize = max((int) $size, 0);
		$this->_validatePageOffset();
		return $this;
	}

	/**
	 * @return int
	 */
	public function getPageCount() {
		if (!$this->getCount()) {
			return 0;
		}
		if (!$this->_pageSize) {
			return 0;
		}
		return ceil($this->getCount() / $this->_pageSize);
	}

	/**
	 * @return bool
	 */
	public function isEmpty() {
		return $this->getCount() == 0;
	}

	/**
	 * Filter result of getItems() by a callback
	 *
	 * @param Closure $filter function(mixed $item): boolean
	 */
	public function filter(Closure $filter) {
		$this->_clearItems();
		$this->_filters[] = $filter;
	}

	/**
	 * @param array|mixed $items
	 */
	public function exclude($items) {
		if (!is_array($items)) {
			$items = array($items);
		}
		if (count($items) == 0) {
			return;
		}

		$comparable = true;
		foreach ($items as $item) {
			$comparable &= ($item instanceof CM_Comparable);
		}

		if ($comparable) {
			$filter = function ($item) use ($items) {
				foreach ($items as $itemExcluded) {
					if ($item->equals($itemExcluded)) {
						return false;
					}
				}
				return true;
			};
		} else {
			$filter = function ($item) use ($items) {
				return !in_array($item, $items);
			};
		}

		$this->filter($filter);
	}

	/**
	 * Items in the underlying source have changed
	 */
	public function _change() {
		if (!$this->_source) {
			throw new CM_Exception('Cannot change paging without source');
		}
		$this->_source->clearCache();
		$this->_clearItems();
		$this->_clearCount();
	}

	/**
	 * @return int Multiple of items per page to load from CM_PagingSource_Abstract
	 */
	protected function _getPageFillRate() {
		return 1 + $this->_getStalenessChance();
	}

	/**
	 * @return float Chance that an item contains stale (non-processable) data (0-1)
	 */
	protected function _getStalenessChance() {
		if ($this->_source) {
			return $this->_source->getStalenessChance();
		}
		return 0;
	}

	/**
	 * @param mixed $itemRaw
	 * @return mixed Processed item
	 * @throws CM_Exception_Nonexistent
	 */
	protected function _processItem($itemRaw) {
		return $itemRaw;
	}

	/**
	 * @param mixed $item
	 * @return boolean Whether the item is matched by any of the registered filters
	 */
	private function _isFilterMatch($item) {
		foreach ($this->_filters as $filter) {
			if (false === $filter($item)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * @return array Raw items (might contain more than $this->_pageSize)
	 */
	private function _getItemsRaw() {
		if ($this->_itemsRaw === null) {
			$this->_itemsRaw = array();
			if ($this->_source) {
				$count = ($this->_pageSize === null) ? null : ceil($this->_pageSize * $this->_getPageFillRate());
				$itemsRaw = $this->_source->getItems($this->_getItemOffset(), $count);
				foreach ($itemsRaw as &$itemRaw) {
					if (is_array($itemRaw) && count($itemRaw) == 1) {
						$itemRaw = reset($itemRaw);
					}
					$this->_itemsRaw[] = $itemRaw;
				}
			}
		}
		return $this->_itemsRaw;
	}

	/**
	 * @return int OR null if no pageSize set
	 */
	private function _getItemOffset() {
		if ($this->_pageSize === null) {
			return null;
		}
		return (int) $this->_pageOffset * $this->_pageSize;
	}

	private function _clearItems() {
		$this->_items = null;
		$this->_itemsRaw = null;
		$this->_itemsPosition = 0;
	}

	private function _clearCount() {
		$this->_count = null;
	}

	/**
	 * @param int $count
	 * @return CM_Paging_Abstract
	 */
	public function _setCount($count) {
		$this->_count = max((int) $count, 0);
		$this->_validatePageOffset();
		return $this;
	}

	private function _validatePageOffset() {
		if ($this->_pageSize !== null) {
			if ($this->_pageOffset * $this->_pageSize >= $this->getCount()) {
				if ($this->_pageSize == 0 || $this->getCount() == 0) {
					$this->_pageOffset = 0;
				} else {
					$this->_pageOffset = ceil($this->getCount() / $this->_pageSize) - 1;
				}
			}
		}
	}

	/* Iterator functions */
	function rewind() {
		$this->_itemsPosition = 0;
	}

	function current() {
		return $this->getItem($this->_itemsPosition);
	}

	function key() {
		return $this->_itemsPosition;
	}

	function next() {
		++$this->_itemsPosition;
	}

	function valid() {
		$items = $this->getItems();
		return isset($items[$this->_itemsPosition]);
	}

}
