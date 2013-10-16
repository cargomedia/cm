<?php

class CM_PagingSource_Array extends CM_PagingSource_Abstract {

	/** @var Closure|null */
	private $_closureFilter, $_closureSortBy;

	/** @var array */
	private $_data;

	/** @var array|null */
	private $_dataRaw;

	/** @var int|null */
	private $_sortFlags, $_sortOrder;

	/** @var CM_PagingSource_Abstract|null */
	private $_source;

	/**
	 * @param array|CM_PagingSource_Abstract $data
	 * @param Closure|null                   $filter    Callback function returning true for items to keep
	 * @param Closure|null                   $sortBy    Callback function returning the value to sort by for each item
	 * @param int|null                       $sortOrder Either SORT_ASC or SORT_DESC
	 * @param int|null                       $sortFlags Sort options, @see array_multisort()
	 * @throws CM_Exception_Invalid
	 */
	public function __construct($data, Closure $filter = null, Closure $sortBy = null, $sortOrder = null, $sortFlags = null) {
		if ($data instanceof CM_PagingSource_Abstract) {
			$this->_source = $data;
		} elseif (is_array($data)) {
			$this->_dataRaw = $data;
		} else {
			throw new CM_Exception_Invalid('Paging data should be either an array or a paging source.');
		}
		$this->_closureFilter = $filter;
		$this->_closureSortBy = $sortBy;
		if (null !== $sortBy) {
			if (null === $sortOrder) {
				$sortOrder = SORT_ASC;
			}
			if (null === $sortFlags) {
				$sortFlags = SORT_REGULAR;
			}
			$this->_sortOrder = (int) $sortOrder;
			$this->_sortFlags = (int) $sortFlags;
		}
	}

	public function clearCache() {
		if ($this->_source) {
			$this->_source->clearCache();
		} else {
			throw new CM_Exception_Invalid('`' . __CLASS__ . '` does not support caching.');
		}
	}

	public function getCount($offset = null, $count = null) {
		return count($this->getItems());
	}

	public function getItems($offset = null, $count = null) {
		return array_slice($this->_getData(), $offset, $count);
	}

	protected function _cacheKeyBase() {
		throw new CM_Exception_Invalid('`' . __CLASS__ . '` does not support caching.');
	}

	protected function _getData() {
		if (isset($this->_data)) {
			return $this->_data;
		}
		if (!isset($this->_dataRaw) && $this->_source) {
			$this->_dataRaw = $this->_source->getItems();
		}
		$this->_data = $this->_dataRaw;
		if (null !== $this->_closureFilter) {
			$this->_data = array_filter($this->_data, $this->_closureFilter);
		}
		if (null !== $this->_closureSortBy) {
			$sortArray = array();
			foreach ($this->_data as $key => $item) {
				$sortArray[$key] = call_user_func($this->_closureSortBy, $item);
			}
			array_multisort($sortArray, $this->_sortOrder, $this->_sortFlags, $this->_data);
		}
		unset($this->_dataRaw);
		return $this->_data;
	}
}
