<?php

class CM_Db_Result {

	/** @var PDOStatement */
	private $_pdoStatement;

	/**
	 * @param PDOStatement $pdoStatement
	 */
	public function __construct(PDOStatement $pdoStatement) {
		$this->_pdoStatement = $pdoStatement;
	}

	/**
	 * @return array|false
	 */
	public function fetch() {
		return $this->_pdoStatement->fetch(PDO::FETCH_ASSOC);
	}

	/**
	 * @param int|null $index
	 * @return mixed|false
	 */
	public function fetchColumn($index = null) {
		$index = (int) $index;
		return $this->_pdoStatement->fetchColumn($index);
	}

	/**
	 * @return array[]
	 */
	public function fetchAll() {
		return $this->_pdoStatement->fetchAll(PDO::FETCH_ASSOC);
	}

	/**
	 * @param int|null $index
	 * @return array
	 */
	public function fetchAllColumn($index = null) {
		$index = (int) $index;
		return $this->_pdoStatement->fetchAll(PDO::FETCH_COLUMN, $index);
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
	 * @return int
	 */
	public function getAffectedRows() {
		return $this->_pdoStatement->rowCount();
	}
}
