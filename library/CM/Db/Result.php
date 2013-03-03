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
	public function fetchAssoc() {
		return $this->_pdoStatement->fetch(PDO::FETCH_ASSOC);
	}

	/**
	 * @return mixed|false
	 */
	public function fetchOne() {
		return $this->_pdoStatement->fetchColumn(0);
	}

	/**
	 * @return array
	 */
	public function fetchCol() {
		return $this->_pdoStatement->fetchAll(PDO::FETCH_COLUMN, 0);
	}

	/**
	 * @return array[]
	 */
	public function fetchAll() {
		return $this->_pdoStatement->fetchAll(PDO::FETCH_ASSOC);
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
