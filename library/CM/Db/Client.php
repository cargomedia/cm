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

	/** @var PDO */
	private $_link;

	/**
	 * @param string      $host
	 * @param int         $port
	 * @param string      $username
	 * @param string      $password
	 * @param string|null $db
	 */
	public function __construct($host, $port, $username, $password, $db = null) {
		$this->_host = (string) $host;
		$this->_port = (int) $port;
		$this->_username = (string) $username;
		$this->_password = (string) $password;
		$this->connect($db);
	}

	/**
	 * @param string|null $db
	 * @throws CM_Db_Exception
	 */
	public function connect($db = null) {
		if ($this->isConnected()) {
			return;
		}
		$dsnOptions = array('host=' . $this->_host, 'port=' . $this->_port);
		if (null !== $db) {
			$dsnOptions[] = 'dbname=' . $db;
		}
		$dsn = 'mysql:' . implode(';', $dsnOptions);
		try {
			$this->_link = new PDO($dsn, $this->_username, $this->_password, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES "UTF8"'));
			$this->_link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch(PDOException $e) {
			throw new CM_Db_Exception('Database connection failed: ' . $e->getMessage());
		}
	}

	public function disconnect() {
		if (!$this->isConnected()) {
			return;
		}
		unset($this->_link);
	}

	/**
	 * @return bool
	 */
	public function isConnected() {
		return isset($this->_link);
	}

	/**
	 * @param string $db
	 */
	public function setDb($db) {
		$this->_link->exec('USE ' . $db);
	}

	/**
	 * @param string $sqlTemplate
	 * @return CM_Db_Statement
	 */
	public function createStatement($sqlTemplate) {
		$statement = $this->_link->prepare($sqlTemplate);
		return new CM_Db_Statement($statement);
	}
}
