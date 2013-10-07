<?php

class CM_PagingSource_Array extends CM_PagingSource_Abstract {

	/**
	 * @var array
	 */
	private $_data;

	/**
	 * @param array        $data
	 * @param Closure|null $filter    Callback function returning true for items to keep
	 * @param Closure|null $sortBy    Callback function returning the value to sort by for each item
	 * @param int|null     $sortOrder Either SORT_ASC or SORT_DESC
	 * @param int|null     $sortFlags Sort options, @see array_multisort()
	 */
	public function __construct(array $data, Closure $filter = null, Closure $sortBy = null, $sortOrder = null, $sortFlags = null) {
		$this->_data = $data;
		if (null !== $filter) {
			$this->_data = array_filter($this->_data, $filter);
		}
		if (null !== $sortBy) {
			$sortArray = array();
			foreach ($this->_data as $key => $item) {
				$sortArray[$key] = $sortBy($item);
			}
			if (null === $sortOrder) {
				$sortOrder = SORT_ASC;
			}
			if (null === $sortFlags) {
				$sortFlags = SORT_REGULAR;
			}
			array_multisort($sortArray, $sortOrder, $sortFlags, $this->_data);
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
