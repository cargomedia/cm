<?php


class CM_MysqlResult {
	private $result;

	/**
	 * Constructor.
	 *
	 * @param resource $result
	 * @return CM_MysqlResult
	 */
	public function __construct($result) {
		$this->result = $result;
	}

	/**
	 * Get number of rows in result.
	 *
	 * @return integer
	 */
	public function numRows() {
		return mysqli_num_rows($this->result);
	}

	/**
	 * Fetch a result row as an object.
	 *
	 * @param string $class_name
	 * @param array $params
	 * @return object an object with string properties that correspond to the fetched row, or FALSE if there are no more rows.
	 */
	public function fetchObject($class_name = null, array $params = null) {
		if (!isset($class_name)) {
			$result_obj = mysqli_fetch_object($this->result);
		} elseif (!isset($params)) {
			$result_obj = mysqli_fetch_object($this->result, $class_name);
		} else {
			$result_obj = mysqli_fetch_object($this->result, $class_name, $params);
		}

		return $result_obj;
	}

	/**
	 * Next row's first Value
	 *
	 * @return mixed|false
	 */
	public function fetchOne() {
		$row = mysqli_fetch_row($this->result);
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
		return mysqli_fetch_assoc($this->result);
	}

	/**
	 * Numeric array of first Values
	 *
	 * @return array
	 */
	public function fetchCol() {
		$col = array();
		while ($row = mysqli_fetch_row($this->result)) {
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
		while ($row = mysqli_fetch_assoc($this->result)) {
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
	 * @param integer $level the amount of columns that are used as indexes.
	 * @param bool $distinctLeaves wether or not the leaves are unique given the specified indexes
	 * @return array
	 */
	public function fetchAllTree($level = 1, $distinctLeaves = true) {
		$result = array();
		while ($row = mysqli_fetch_assoc($this->result)) {
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
	 *
	 * @return bool
	 */
	public function free() {
		if (!is_resource($this->result)) {
			return false;
		}
		return mysqli_free_result($this->result);
	}
}
