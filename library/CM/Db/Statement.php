<?php

class CM_Db_Statement {

	/** @var PDOStatement */
	private $_statement;

	/**
	 * @param PDOStatement $statement
	 */
	public function __construct(PDOStatement $statement) {
		$this->_statement = $statement;
	}

	/**
	 * @param array|null $parameters
	 * @throws CM_Db_Exception
	 * @return CM_Db_Result
	 */
	public function execute(array $parameters = null) {
		try {
			$this->_statement->execute($parameters);
		} catch (PDOException $e) {
			throw new CM_Db_Exception('Query execution failed: ' . $e->getMessage() . ' (query: `' . $this->_statement->queryString . '`)');
		}
		return new CM_Db_Result($this->_statement);
	}
}
