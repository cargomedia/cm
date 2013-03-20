<?php

class CM_Db_Client {

	/** @var string */
	private $_host;

	/** @var int */
	private $_port;

	/** @var string */
	private $_username;

	/** @var string */
	private $_password;

	/** @var string|null */
	private $_db;

	/** @var PDO */
	private $_pdo;

	/** @var int */
	private $_lastConnect;

	/** @var int|null */
	private $_reconnectTimeout;

	/**
	 * @param string      $host
	 * @param int         $port
	 * @param string      $username
	 * @param string      $password
	 * @param string|null $db
	 * @param int|null    $reconnectTimeout
	 */
	public function __construct($host, $port, $username, $password, $db = null, $reconnectTimeout = null) {
		$this->_host = (string) $host;
		$this->_port = (int) $port;
		$this->_username = (string) $username;
		$this->_password = (string) $password;
		if (null !== $db) {
			$this->_db = (string) $db;
		}
		if (null !== $reconnectTimeout) {
			$this->_reconnectTimeout = (int) $reconnectTimeout;
		}
		$this->connect();
	}

	/**
	 * @throws CM_Db_Exception
	 */
	public function connect() {
		$this->_lastConnect = time();
		if ($this->isConnected()) {
			return;
		}
		$dsnOptions = array('host=' . $this->_host, 'port=' . $this->_port);
		if (null !== $this->_db) {
			$dsnOptions[] = 'dbname=' . $this->_db;
		}
		$dsn = 'mysql:' . implode(';', $dsnOptions);
		try {
			$this->_pdo = new PDO($dsn, $this->_username, $this->_password, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES "UTF8"'));
			$this->_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch (PDOException $e) {
			throw new CM_Db_Exception('Database connection failed: ' . $e->getMessage());
		}
	}

	public function disconnect() {
		if (!$this->isConnected()) {
			return;
		}
		unset($this->_pdo);
	}

	/**
	 * @return bool
	 */
	public function isConnected() {
		return isset($this->_pdo);
	}

	/**
	 * @param string $db
	 */
	public function setDb($db) {
		$this->_pdo->exec('USE ' . $db);
	}

	/**
	 * @param string $sqlTemplate
	 * @return CM_Db_Statement
	 */
	public function createStatement($sqlTemplate) {
		if (null !== $this->_reconnectTimeout && ($this->getLastConnectTime() + $this->_reconnectTimeout) < time()) {
			$this->disconnect();
			$this->connect();
		}
		return new CM_Db_Statement($this->createPdoStatement($sqlTemplate), $this);
	}

	/**
	 * @param $sqlTemplate
	 * @throws CM_Db_Exception
	 * @return PDOStatement
	 */
	public function createPdoStatement($sqlTemplate) {
		if (!$this->isConnected()) {
			$this->connect();
		}

		$retryCount = 1;
		for ($try = 0; true; $try++) {
			try {
				return @$this->_pdo->prepare($sqlTemplate);
			} catch (PDOException $e) {
				if ($try < $retryCount && $this->isConnectionLossError($e)) {
					$this->disconnect();
					$this->connect();
					continue;
				}
				throw new CM_Db_Exception('Cannot prepare statement (retried ' . $try . 'x): ' . $e->getMessage());
			}
		}
		throw new CM_Db_Exception('Line should never be reached');
	}

	/**
	 * @return string|null
	 */
	public function getLastInsertId() {
		$lastInsertId = $this->_pdo->lastInsertId();
		if (!$lastInsertId) {
			return null;
		}
		return $lastInsertId;
	}

	/**
	 * @return int
	 */
	public function getLastConnectTime() {
		return $this->_lastConnect;
	}

	/**
	 * @param PDOException $exception
	 * @return bool
	 */
	public function isConnectionLossError(PDOException $exception) {
		$sqlState = $exception->errorInfo[0];
		$driverCode = $exception->errorInfo[1];
		$driverMessage = $exception->errorInfo[2];
		if (
			(1053 === $driverCode && false !== stripos($driverMessage, 'Server shutdown in progress')) ||
			(1317 === $driverCode && false !== stripos($driverMessage, 'Query execution was interrupted')) ||
			(2006 === $driverCode && false !== stripos($driverMessage, 'MySQL server has gone away')) ||
			(2013 === $driverCode && false !== stripos($driverMessage, 'Lost connection to MySQL server')) ||
			(2055 === $driverCode && false !== stripos($driverMessage, 'Lost connection to MySQL server'))
		) {
			return true;
		}
		return false;
	}

	/**
	 * @param string $name
	 * @return string
	 */
	public function quoteIdentifier($name) {
		return '`' . str_replace('`', '``', $name) . '`';
	}
}
