<?php

class CM_PagingSource_Sql extends CM_PagingSource_Abstract {
	private $_select, $_table, $_where, $_order, $_join, $_group;
	protected $_dbSlave = false;

	/**
	 * @param string $select
	 * @param string $table
	 * @param string $where
	 * @param string $order
	 * @param string $join
	 * @param string $group
	 */
	function __construct($select, $table, $where = null, $order = null, $join = null, $group = null) {
		$this->_select = $select;
		$this->_table = $table;
		$this->_where = $where;
		$this->_order = $order;
		$this->_join = $join;
		$this->_group = $group;
	}

	protected function _cacheKeyBase() {
		return array($this->_table, $this->_where, $this->_join);
	}

	public function getCount($offset = null, $count = null) {
		$cacheKey = array('count');
		if (($count = $this->_cacheGet($cacheKey)) === false) {
			if ($this->_group) {
				$select = '1';
			} else {
				if (stripos($this->_select, 'DISTINCT') === 0) {
					$select = 'COUNT(' . $this->_select . ')';
				} else {
					$select = 'COUNT(*)';
				}
			}
			$query = 'SELECT ' . $select . ' FROM `' . $this->_table . '`';
			if ($this->_join) {
				$query .= ' ' . $this->_join;
			}
			if ($this->_where) {
				$query .= ' WHERE ' . $this->_where;
			}
			if ($this->_group) {
				$query .= ' GROUP BY ' . $this->_group;
			}
			$result = CM_Mysql::query($query, $this->_dbSlave);
			if ($this->_group) {
				$count = (int) $result->numRows();
			} else {
				$count = (int) $result->fetchOne();
			}
			$this->_cacheSet($cacheKey, $count);
		}
		return $count;
	}

	public function getItems($offset = null, $count = null) {
		$cacheKey = array('items', $this->_select, $this->_order, $offset, $count);
		if (($items = $this->_cacheGet($cacheKey)) === false) {
			$query = 'SELECT ' . $this->_select . ' FROM `' . $this->_table . '`';
			if ($this->_join) {
				$query .= ' ' . $this->_join;
			}
			if ($this->_where) {
				$query .= ' WHERE ' . $this->_where;
			}
			if ($this->_group) {
				$query .= ' GROUP BY ' . $this->_group;
			}
			if ($this->_order) {
				$query .= ' ORDER BY ' . $this->_order;
			}
			if ($offset !== null && $count !== null) {
				$query .= ' LIMIT ' . $offset . ',' . $count;
			}
			$result = CM_Mysql::query($query, $this->_dbSlave);
			$items = $result->fetchAll();
			$this->_cacheSet($cacheKey, $items);
		}
		return $items;
	}

	public function getStalenessChance() {
		return 0.01;
	}
}
