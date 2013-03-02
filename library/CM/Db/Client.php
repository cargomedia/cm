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

	/** @var mysqli */
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
		$this->_link = $this->_connect($db);
	}

	/**
	 * @param string $db
	 * @throws CM_Exception
	 */
	public function selectDb($db) {
		if (!$this->_link->select_db($db)) {
			throw new CM_Exception('Cannot select database `' . $db . '`');
		}
	}

	/**
	 * @param string    $query
	 * @throws CM_Exception
	 * @return CM_MysqlResult|bool
	 */
	public function query($query) {
		$result = self::_callRetried(function (mysqli $link) use ($query) {
			return @$link->query($query);
		});
		if (true === $result) {
			return true;
		}
		return new CM_MysqlResult($result);
	}

	/**
	 * @return int|string|bool Last insert query autoincrement id or FALSE if none available
	 */
	public function getInsertId() {
		$insertId = $this->_link->insert_id;
		if (0 === $insertId) {
			return false;
		}
		return $insertId;
	}

	/**
	 * @return int|string|bool Number of affected rows or FALSE on error
	 */
	public function getAffectedRows() {
		$affectedRows = $this->_link->affected_rows;
		if ($affectedRows < 0) {
			return false;
		}
		return $affectedRows;
	}

	/**
	 * @param $string
	 * @return string
	 */
	public function escapeString($string) {
		return self::_callRetried(function (mysqli $link) use ($string) {
			return @$link->real_escape_string($string);
		}, false);
	}

	/**
	 * @param string|null $db
	 * @throws CM_Exception
	 * @return mysqli
	 */
	private function _connect($db = null) {
		$link = @new mysqli($this->_host, $this->_username, $this->_password, $db, $this->_port);
		if ($link->connect_error) {
			throw new CM_Exception('Database connection failed: ' . $link->connect_error);
		}

		if (!$link->set_charset('utf8')) {
			throw new CM_Exception('Cannot set database charset to utf-8');
		}
		return $link;
	}

	/**
	 * @return bool
	 */
	private function _getLastCallShouldRetry() {
		if (0 === $this->_link->errno) {
			return false;
		}
		$retryErrnoList = array(
			1317, // Query execution was interrupted
			2006, // MySQL server has gone away
		);
		return in_array($this->_link->errno, $retryErrnoList);
	}

	/**
	 * @param Closure $callback fn(mysqli)
	 * @throws CM_Exception
	 * @return mixed
	 */
	private function _callRetried(Closure $callback) {
		$retryCount = 1;
		$try = 0;
		do {
			$result = $callback($this->_link);
			$try++;
		} while ($this->_getLastCallShouldRetry() && $try <= $retryCount);

		if (0 !== $this->_link->errno) {
			throw new CM_Exception('Mysql error `' . $this->_link->errno . '` with message `' . $this->_link->error . '`');
		}

		return $result;
	}
}
