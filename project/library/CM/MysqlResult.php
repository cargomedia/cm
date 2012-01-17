<?php


class CM_MysqlResult {
	/**
	 * @var mysqli_result
	 */
	private $_result;

	/**
	 * @param mysqli_result $result
	 * @return CM_MysqlResult
	 */
	public function __construct(mysqli_result $result) {
		$this->_result = $result;
	}

	/**
	 * Get number of rows in result.
	 *
	 * @return integer
	 */
	public function numRows() {
		return $this->_result->num_rows;
	}

	/**
	 * Fetch a result row as an object.
	 *
	 * @param string $class_name
	 * @param array  $params
	 * @return object an object with string properties that correspond to the fetched row, or FALSE if there are no more rows.
	 */
	public function fetchObject($class_name = null, array $params = null) {
		if (!isset($class_name)) {
			$result_obj = $this->_result->fetch_object();
		} elseif (!isset($params)) {
			$result_obj = $this->_result->fetch_object($class_name);
		} else {
			$result_obj = $this->_result->fetch_object($class_name, $params);
		}

		return $result_obj;
	}

	/**
	 * Next row's first Value
	 *
	 * @return mixed|false
	 */
	public function fetchOne() {
		$row = $this->_result->fetch_row();
		if (is_null($row)) {
			return false;
		}
		return reset($row);
	}

	/**
	 * Next row as associative Field=>Value array
	 *
	 * @return array|false
	 */
	public function fetchAssoc() {
		return $this->_result->fetch_assoc();
	}

	/**
	 * Numeric array of first Values
	 *
	 * @return array
	 */
	public function fetchCol() {
		$col = array();
		while ($row = $this->_result->fetch_row()) {
			$col[] = $row[0];
		}
		return $col;
	}

	/**
	 * Numeric array of associative Field=>Value arrays
	 *
	 * @return array
	 */
	public function fetchAll() {
		$result = array();
		while ($row = $this->_result->fetch_assoc()) {
			$result[] = $row;
		}
		return $result;
	}

	/**
	 * A tree with $level tiers. The children of the rootnode have the distinct value of the first column as key and contain all the rows
	 * with this key as first value. The children of such a node have the distinct values of the second column as key and contain all the
	 * rows which have the the key of their grandparent as first value and the key of their parent as second value. And so on.
	 * The amount of leaf nodes corresponds to the amount of rows in the resultset.
	 * Each leaf node contains an array consisting of the $rowcount - $level last entries of the row it represents. Or a scalar in the
	 * case of $level = $rowcount -1.
	 *
	 * @param integer $level          the amount of columns that are used as indexes.
	 * @param bool    $distinctLeaves wether or not the leaves are unique given the specified indexes
	 * @return array
	 */
	public function fetchAllTree($level = 1, $distinctLeaves = true) {
		$result = array();
		while ($row = $this->_result->fetch_assoc()) {
			$resultEntry = &$result;
			for ($i = 0; $i < $level; $i++) {
				if (!is_array($resultEntry)) {
					$resultEntry = array();
				}
				$value = array_shift($row);
				$resultEntry = &$resultEntry[$value];
			}
			if (count($row) <= 1) {
				$row = reset($row);
			}
			if ($distinctLeaves) {
				$resultEntry = $row;
			} else {
				$resultEntry[] = $row;
			}
		}
		return $result;
	}

	/**
	 * Free result memory.
	 */
	public function free() {
		$this->_result->free();
	}
}
