<?php

class CM_Db_Statement {

	/** @var PDOStatement */
	private $_pdoStatement;

	/** @var CM_Db_Client */
	private $_client;

	/**
	 * @param PDOStatement $pdoStatement
	 * @param CM_Db_Client $client
	 */
	public function __construct(PDOStatement $pdoStatement, CM_Db_Client $client) {
		$this->_pdoStatement = $pdoStatement;
		$this->_client = $client;
	}

	/**
	 * @param array|null $parameters
	 * @throws CM_Db_Exception
	 * @return CM_Db_Result
	 */
	public function execute(array $parameters = null) {
		$retryCount = 1;
		for ($try = 0; true; $try++) {
			try {
				@$this->_pdoStatement->execute($parameters);
				return new CM_Db_Result($this->_pdoStatement);
			} catch (PDOException $e) {
				if ($try < $retryCount && $this->_client->isConnectionLossError($e)) {
					$this->_client->disconnect();
					$this->_client->connect();
					$this->_pdoStatement = $this->_client->createPdoStatement($this->getQueryString());
					continue;
				}
				throw new CM_Db_Exception('Cannot execute statement: ' . $e->getMessage() . ' (query: `' . $this->_pdoStatement->queryString . '`)');
			}
		}
		throw new CM_Db_Exception('Line should never be reached');
	}

	/**
	 * @return string
	 */
	public function getQueryString() {
		return $this->_pdoStatement->queryString;
	}
}
