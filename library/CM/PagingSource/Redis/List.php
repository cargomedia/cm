<?php

class CM_PagingSource_Redis_List extends CM_PagingSource_Abstract {

	/** @var string */
	private $_key;

	/** @var CM_Redis_Client */
	private $_client;

	/**
	 * @param string $key
	 */
	public function __construct($key) {
		$this->_key = (string) $key;
		$this->_client = CM_Redis_Client::getInstance();
	}

	protected function _cacheKeyBase() {
		return array($this->_key);
	}

	public function getCount($offset = null, $count = null) {
		$cacheKey = array('count');
		if (($count = $this->_cacheGet($cacheKey)) === false) {
			$count = $this->_client->lLen($this->_key);
			$this->_cacheSet($cacheKey, $count);
		}
		return $count;
	}

	public function getItems($offset = null, $count = null) {
		$cacheKey = array('items', $offset, $count);
		if (($items = $this->_cacheGet($cacheKey)) === false) {
			$stop = null;
			if ($count !== null) {
				$stop = $offset + $count - 1;
			}
			$items = $this->_client->lRange($this->_key, $offset, $stop);
			$this->_cacheSet($cacheKey, $items);
		}
		return $items;
	}

	public function getStalenessChance() {
		return 0.01;
	}
}
