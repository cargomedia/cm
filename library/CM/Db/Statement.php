<?php

class CM_Db_Statement {

	/** @var PDOStatement */
	private $_statement;

	/** @var CM_Db_Client */
	private $_client;

	/**
	 * @param PDOStatement $statement
	 * @param CM_Db_Client $client
	 */
	public function __construct(PDOStatement $statement, CM_Db_Client $client) {
		$this->_statement = $statement;
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
				@$this->_statement->execute($parameters);
				break;
			} catch (PDOException $e) {
				if ($try >= $retryCount || !$this->_getShouldReconnectAndRetry($e)) {
					throw new CM_Db_Exception('Query execution failed: ' . $e->getMessage() . ' (query: `' . $this->_statement->queryString . '`)');
				}
				$this->_client->disconnect();
				$this->_client->connect();
			}
		}

		return new CM_Db_Result($this->_statement);
	}

	/**
	 * @param PDOException $exception
	 * @return bool
	 */
	private function _getShouldReconnectAndRetry(PDOException $exception) {
		$sqlState = $exception->errorInfo[0];
		$driverCode = $exception->errorInfo[1];
		$driverMessage = $exception->errorInfo[2];

		if (1317 === $driverCode && false !== stripos('Query execution was interrupted', $driverMessage)) {
			return true;
		}
		if (2006 === $driverCode && false !== stripos('MySQL server has gone away', $driverMessage)) {
			return true;
		}

		return false;
	}
}
