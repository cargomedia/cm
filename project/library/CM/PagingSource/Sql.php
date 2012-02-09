<?php

class CM_PagingSource_Sql extends CM_PagingSource_Abstract {
	private $_select, $_table, $_where, $_order, $_join, $_group, $_queryCount = 0;
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
		foreach (func_get_args() as $arg) {
			if (is_array($arg)) {
				if ($this->_queryCount && $this->_queryCount != count($arg)) {
					throw new CM_Exception_Invalid('Cannot do a union with an inconsistent amount of parameters.');
				}
				$this->_queryCount = count($arg);
			}
		}
		if (!$this->_queryCount) {
			$this->_queryCount = 1;
		}
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
			$query = '';
			for ($i = 0 ; $i < $this->_queryCount; $i++) {
				if ($this->_group) {
					$select = '1';
				} elseif ($this->_queryCount > 1) {
					if (is_array($this->_select)) {
						$select =  $this->_select[$i];
					} else {
						$select = $this->_select;
					}
				} else {
					$select = 'COUNT(*)';
				}
				if ($i > 0) {
					$query .= ' UNION ';
				}
				$query .= 'SELECT ' . $select . ' FROM `' . $this->_table . '`';
				if ($this->_join) {
					if (is_array($this->_join)) {
						$query .= ' ' .$this->_join[$i];
					} else {
						$query .= ' ' . $this->_join;
					}
				}
				if ($this->_where) {
					if (is_array($this->_where)) {
						$query .= ' WHERE ' .$this->_where[$i];
					} else {
						$query .= ' WHERE ' . $this->_where;
					}
				}
				if ($this->_group) {
					if (is_array($this->_group)) {
						$query .= ' GROUP BY ' .$this->_group[$i];
					} else {
						$query .= ' GROUP BY ' . $this->_group;
					}
				}
			}
			$result = CM_Mysql::query($query, $this->_dbSlave);
			if ($this->_group || ($this->_queryCount > 1)) {
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
			$query = '';
			for ($i = 0; $i < $this->_queryCount; $i++) {
				if ($i > 0) {
					$query .= ' UNION ';
				}
				if (is_array($this->_select)) {
					$query .= 'SELECT ' . $this->_select[$i];
				} else {
					$query .= 'SELECT ' . $this->_select;
				}
				if (is_array($this->_table)) {
					$query .= ' FROM `' . $this->_table[$i] . '`';
				} else {
					$query .= ' FROM `' . $this->_table . '`';
				}
				if ($this->_join) {
					if (is_array($this->_join)) {
						$query .= ' ' .$this->_join[$i];
					} else {
						$query .= ' ' . $this->_join;
					}
				}
				if ($this->_where) {
					if (is_array($this->_where)) {
						$query .= ' WHERE ' .$this->_where[$i];
					} else {
						$query .= ' WHERE ' . $this->_where;
					}
				}
				if ($this->_group) {
					if (is_array($this->_group)) {
						$query .= ' GROUP BY ' .$this->_group[$i];
					} else {
						$query .= ' GROUP BY ' . $this->_group;
					}
				}
				if ($this->_order) {
					if (is_array($this->_order)) {
						$query .= ' ORDER BY ' .$this->_order[$i];
					} else {
						$query .= ' ORDER BY ' . $this->_order;
					}
				}
				if ($offset !== null && $count !== null) {
					$query .= ' LIMIT ' . $offset . ',' . $count;
				}
			}
			$result = CM_Mysql::query($query, $this->_dbSlave);
			$items = $result->fetchAll();
			$this->_cacheSet($cacheKey, $items);
		}
		return $items;
	}
}
