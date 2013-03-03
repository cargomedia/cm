<?php

class CM_Db_Result {

	/** @var PDOStatement */
	private $_statement;

	/**
	 * @param PDOStatement $statement
	 */
	public function __construct(PDOStatement $statement) {
		$this->_statement = $statement;
	}

	/**
	 * @return array|false
	 */
	public function fetchAssoc() {
		return $this->_statement->fetch(PDO::FETCH_ASSOC);
	}

	/**
	 * @return mixed|false
	 */
	public function fetchOne() {
		return $this->_statement->fetchColumn(0);
	}

	/**
	 * @return array
	 */
	public function fetchCol() {
		return $this->_statement->fetchAll(PDO::FETCH_COLUMN, 0);
	}

	/**
	 * @return array[]
	 */
	public function fetchAll() {
		return $this->_statement->fetchAll(PDO::FETCH_ASSOC);
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
		return $this->_statement->rowCount();
	}
}
