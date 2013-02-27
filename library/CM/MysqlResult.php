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
	 * @deprecated
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
	 * @return array|null
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
	 * @param int|null     $level
	 * @param bool|null    $distinctLeaves
	 * @return array[]
	 */
	public function fetchAllTree($level = null, $distinctLeaves = null) {
		return CM_Util::getArrayTree($this->fetchAll(), $level, $distinctLeaves);
	}

	/**
	 * Free result memory.
	 */
	public function free() {
		$this->_result->free();
	}
}
