<?php

class CM_Mysql extends CM_Class_Abstract {
	const STMT_INSERT = 'INSERT';
	const STMT_INSERT_IGNORE = 'INSERT IGNORE';
	const STMT_INSERT_DELAYED = 'INSERT DELAYED';
	const STMT_REPLACE = 'REPLACE';
	const STMT_REPLACE_DELAYED = 'REPLACE DELAYED';

	private static $_link;
	private static $_linkReadOnly;

	/**
	 * @param bool $readOnly OPTIONAL
	 * @return resource
	 */
	public static function connect($readOnly = false) {
		$config = self::_getConfig();
		$link = & self::$_link;
		$server = $config->server;
		if ($readOnly) {
			$link = & self::$_linkReadOnly;
			if (!empty($config->servers_read)) {
				$server = $config->servers_read[array_rand($config->servers_read)];
			}
		}

		$link = new mysqli($server['host'], $config->user, $config->pass, null, $server['port']);
		if ($link->connect_error) {
			throw new CM_Exception('Database connection failed: ' . $link->connect_error);
		}
		self::selectDb($config->db, $readOnly);
		if (!$link->set_charset('utf8')) {
			throw new CM_Exception('Cannot set database charset to utf-8');
		}
		return $link;
	}

	/**
	 * @param string $db
	 * @param bool   $readOnly
	 * @throws CM_Exception
	 */
	public static function selectDb($db, $readOnly = false) {
		$link = self::_getLink($readOnly);
		if (!$link->select_db($db)) {
			throw new CM_Mysql_DbSelectException('Cannot select database `' . $db . '`');
		}
	}

	/**
	 * Compile query placeholder.
	 *
	 * @param array $query_tpl
	 * @return string
	 */
	public static function compile_placeholder($query_tpl) {
		// Replace TBL_* constants
		$query_tpl = preg_replace_callback('/(TBL_.+?)\b/', function ($matches) {
			return '`' . constant($matches[1]) . '`';
		}, $query_tpl);

		$compiled = array();
		$i = 0; // placeholders counter
		$p = 0; // current position
		$prev_p = 0; // previous position

		while (false !== ($p = strpos($query_tpl, '?', $p))) {
			$compiled[] = substr($query_tpl, $prev_p, $p - $prev_p);

			$type_char = $char = $query_tpl{$p - 1};

			switch ($type_char) {
				case '"':
				case "'":
				case '`':
					$type = $type_char; // string
					break;
				default:
					$type = ''; // integer
					break;
			}

			$next_char = isset($query_tpl{$p + 1}) ? $query_tpl{$p + 1} : null;
			if ($next_char === '@') { // array list
				$compiled[] = array($i++, $type, '@');
				$prev_p = ($p = $p + 2);
			} else {
				$compiled[] = array($i++, $type);
				$prev_p = ($p = $p + 1);
			}
		}

		$tail_length = (strlen($query_tpl) - $prev_p);
		if ($tail_length) {
			$compiled[] = substr($query_tpl, -$tail_length);
		}

		return $compiled;
	}

	/**
	 * Generates a query string for execution.
	 *
	 * @param string $query_tpl
	 * @param mixed  $arg1
	 * @param mixed  $arg2
	 * @param mixed  $arg3...
	 * @return string
	 */
	public static function placeholder() {
		$arguments = func_get_args();
		$c_query = array_shift($arguments);
		if (!is_array($c_query)) {
			$c_query = self::compile_placeholder($c_query);
		}

		$query = '';

		foreach ($c_query as $piece) {
			if (!is_array($piece)) {
				$query .= $piece;
				continue;
			}

			list($index, $type) = $piece;

			if (isset($piece[2])) {
				// array value
				$array = $arguments[$index];

				switch ($type) {
					case '"':
					case "'":
					case '`':
						foreach ($array as &$var) {
							$var = self::_getLink()->real_escape_string($var);
						}
						$query .= implode("$type,$type", $array);
						break;
					default:
						$query .= implode(",", array_map('intval', $array));
						break;
				}
			} else {
				// scalar value
				$var = $arguments[$index];

				switch ($type) {
					case '"':
					case "'":
					case '`':
						$query .= self::_getLink()->real_escape_string($var);
						break;
					default:
						$query .= round($var, 0);
						break;
				}
			}
		}

		return $query;
	}

	/**
	 * Sends query to a database server.
	 *
	 * @param string $query
	 * @param bool   $readOnly
	 * @throws CM_Exception
	 * @return CM_MysqlResult|true
	 */
	public static function query($query, $readOnly = false) {
		$readOnly ? CM_Debug::get()->incStats('mysql-read', $query) : CM_Debug::get()->incStats('mysql', $query);

		$link = self::_getLink($readOnly);
		/** @var mysqli_result $result */
		$result = $link->query($query);

		if ($result instanceof MySQLi_Result) {
			return new CM_MysqlResult($result);
		} elseif (true === $result) {
			return true;
		} else {
			throw new CM_Exception('Mysql error `' . $link->errno . '` with message `' . $link->error . '` (query: `' . $query . '`)');
		}
	}

	/**
	 * Compile and execute a query.
	 *
	 * @param string $query Can contain ?, @? as placeholders
	 * @param mixed  $arg1
	 * @param mixed  $arg2  ...
	 * @return CM_MysqlResult|int|false Either a CM_MysqlResult, last insert id or affected rows.
	 */
	public static function exec() {
		$query = call_user_func_array(array('self', 'placeholder'), func_get_args());
		$result = self::query($query);
		if ($result instanceof CM_MysqlResult) {
			return $result;
		} elseif ($insertId = self::getInsertId()) {
			return $insertId;
		} else {
			return self::getAffectedRows();
		}
	}

	/**
	 * Compile and execute a read-only SELECT query.
	 * This might return somewhat stale data
	 *
	 * @param string $query Can contain ?, @? as placeholders
	 * @param mixed  $arg1
	 * @param mixed  $arg2  ...
	 * @return CM_MysqlResult
	 */
	public static function execRead() {
		$query = call_user_func_array(array('self', 'placeholder'), func_get_args());
		return self::query($query, self::_getConfig()->serversReadEnabled);
	}

	/**
	 * Select fields from a table
	 *
	 * @param string            $table
	 * @param string|array      $attrs Column-name OR Column-names array
	 * @param string|array|null $where Associative array field=>value OR string
	 * @param string|null       $order
	 * @return CM_MysqlResult
	 */
	public static function select($table, $attrs, $where = null, $order = null) {
		$attrs = (array) $attrs;
		foreach ($attrs as &$attr) {
			if ($attr == '*') {
				$attr = '*';
			} else {
				$attr = self::placeholder("`?`", $attr);
			}
		}
		$query = 'SELECT ' . implode(',', $attrs) . ' FROM `' . $table . '` ' . self::_queryWhere($where);
		if ($order) {
			$query .= ' ORDER BY ' . $order;
		}
		return self::query($query);
	}

	/**
	 * Select COUNT(*) from a table
	 *
	 * @param string            $table
	 * @param string|array|null $where Associative array field=>value OR string
	 * @return int
	 */
	public static function count($table, $where = null) {
		$query = 'SELECT COUNT(*) FROM `' . $table . '` ' . self::_queryWhere($where);
		return (int) self::query($query)->fetchOne();
	}

	/**
	 * Insert one/multiple rows
	 *
	 * @param string            $table
	 * @param string|array      $attr           Column-name OR Column-names array OR associative field=>value pair
	 * @param string|array|null $value          Column-value OR Column-values array OR Multiple Column-values array(array)
	 * @param array|null        $onDuplicateKeyValues
	 * @param string            $statement
	 * @return string Insert Id
	 */
	public static function insert($table, $attr, $value = null, array $onDuplicateKeyValues = null, $statement = self::STMT_INSERT) {
		if ($value === null && is_array($attr)) {
			$value = array_values($attr);
			$attr = array_keys($attr);
		}
		$values = (array) $value;
		$attrs = (array) $attr;
		if (!is_array(reset($values))) {
			if (count($attrs) == 1) {
				foreach ($values as &$value) {
					$value = array($value);
				}
			} else {
				$values = array($values);
			}
		}
		$attrsEscaped = array();
		foreach ($attrs as $attr) {
			$attrsEscaped[] = self::placeholder("`?`", $attr);
		}
		$rowsEscaped = array();
		foreach ($values as &$value) {
			$row = (array) $value;
			if (count($row) != count($attrs)) {
				throw new CM_Exception('Row size does not match number of fields');
			}
			$rowEscaped = array();
			foreach ($row as $rowValue) {
				if ($rowValue === null) {
					$rowEscaped[] = 'NULL';
				} else {
					$rowEscaped[] = self::placeholder("'?'", $rowValue);
				}
			}
			$rowsEscaped[] = '(' . implode(',', $rowEscaped) . ')';
		}

		$query = $statement . ' INTO `' . $table . '` (' . implode(',', $attrsEscaped) . ') VALUES ' . implode(',', $rowsEscaped);
		if ($onDuplicateKeyValues) {
			$valuesEscaped = array();
			foreach ($onDuplicateKeyValues as $attr => $value) {
				if ($value === null) {
					$valuesEscaped[] = self::placeholder("`?`=NULL", $attr);
				} else {
					$valuesEscaped[] = self::placeholder("`?`='?'", $attr, $value);
				}
			}
			$query .= ' ON DUPLICATE KEY UPDATE ' . implode(',', $valuesEscaped);
		}
		self::query($query);
		return self::getInsertId();
	}

	/**
	 * @param string            $table
	 * @param string|array      $attr  Column-name OR Column-names array OR associative field=>value pair
	 * @param string|array|null $value Column-value OR Column-values array OR Multiple Column-values array(array)
	 * @return int Insert Id
	 */
	public static function insertIgnore($table, $attr, $value = null) {
		return self::insert($table, $attr, $value, null, self::STMT_INSERT_IGNORE);
	}

	/**
	 * @param string            $table
	 * @param string|array      $attr  Column-name OR Column-names array OR associative field=>value pair
	 * @param string|array|null $value Column-value OR Column-values array OR Multiple Column-values array(array)
	 * @param array|null        $onDuplicateKeyValues
	 * @return int Insert Id
	 */
	public static function insertDelayed($table, $attr, $value = null, array $onDuplicateKeyValues = null) {
		if (self::_delayedEnabled()) {
			return self::insert($table, $attr, $value, $onDuplicateKeyValues, self::STMT_INSERT_DELAYED);
		} else {
			return self::insert($table, $attr, $value, $onDuplicateKeyValues, self::STMT_INSERT);
		}
	}

	/**
	 * @param string            $table
	 * @param string|array      $attr  Column-name OR Column-names array OR associative field=>value pair
	 * @param string|array|null $value OPTIONAL Column-value OR Column-values array OR Multiple Column-values array(array)
	 * @return int Insert Id
	 */
	public static function replace($table, $attr, $value = null) {
		return self::insert($table, $attr, $value, null, self::STMT_REPLACE);
	}

	/**
	 * @param string            $table
	 * @param string|array      $attr  Column-name OR Column-names array OR associative field=>value pair
	 * @param string|array|null $value OPTIONAL Column-value OR Column-values array OR Multiple Column-values array(array)
	 * @return int Insert Id
	 */
	public static function replaceDelayed($table, $attr, $value = null) {
		if (self::_delayedEnabled()) {
			return self::insert($table, $attr, $value, null, self::STMT_REPLACE_DELAYED);
		} else {
			return self::insert($table, $attr, $value, null, self::STMT_REPLACE);
		}
	}

	/**
	 * @param string            $table
	 * @param array             $values Associative array field=>value
	 * @param string|array|null $where  Associative array field=>value OR string
	 * @return int Affected rows
	 */
	public static function update($table, array $values, $where = null) {
		if (empty($values)) {
			return 0;
		}
		$valuesEscaped = array();
		foreach ($values as $attr => $value) {
			if ($value === null) {
				$valuesEscaped[] = self::placeholder("`?`=NULL", $attr);
			} else {
				$valuesEscaped[] = self::placeholder("`?`='?'", $attr, $value);
			}
		}
		$query = 'UPDATE `' . $table . '` SET ' . implode(',', $valuesEscaped) . ' ' . self::_queryWhere($where);
		self::query($query);
		return self::getAffectedRows();
	}

	/**
	 * @param string      $table
	 * @param string      $column
	 * @param array|null  $where
	 * @param int         $value
	 * @param array       $whereRow
	 * @throws CM_Exception_Invalid
	 */
	public static function updateSequence($table, $column, array $where = null, $value, array $whereRow) {
		$table = (string) $table;
		$column = (string) $column;
		if (null === $where) {
			$where = array();
		}
		$value = (int) $value;
		if ($value <= 0 || $value > self::count($table, $where)) {
			throw new CM_Exception_Invalid('Sequence out of bounds.');
		}
		$valueOld = self::select($table, $column, array_merge($whereRow, $where))->fetchOne();
		if (false === $valueOld) {
			throw new CM_Exception_Invalid('Could not retrieve original sequence number.');
		}
		$valueOld = (int) $valueOld;

		if ($value > $valueOld) {
			$upperBound = $value;
			$lowerBound = $valueOld;
			$direction = -1;
		} else {
			$upperBound = $valueOld;
			$lowerBound = $value;
			$direction = 1;
		}
		if (empty($where)) {
			$queryWhere = 'WHERE 1';
		} else {
			$queryWhere = self::_queryWhere($where);
		}
		CM_Mysql::exec("UPDATE `?` SET `?` = `?` + ? " . $queryWhere .
				" AND `?` BETWEEN ? AND ?", $table, $column, $column, $direction, $column, $lowerBound, $upperBound);
		CM_Mysql::update($table, array($column => $value), array_merge($whereRow, $where));
	}

	/**
	 * @param string      $table
	 * @param string      $column
	 * @param array|null  $where
	 * @param array       $whereRow
	 */
	public static function deleteSequence($table, $column, array $where = null, array $whereRow) {
		$table = (string) $table;
		$column = (string) $column;
		$sequenceMax = self::count($table, $where);
		if ($sequenceMax) {
			self::updateSequence($table, $column, $where, $sequenceMax, $whereRow);
			self::delete($table, array_merge($whereRow, $where));
		}
	}

	/**
	 * @param string       $table
	 * @param string|array $where Associative array field=>value OR string
	 * @return int Affected rows
	 */
	public static function delete($table, $where) {
		$query = 'DELETE FROM `' . $table . '` ' . self::_queryWhere($where);
		self::query($query);
		return self::getAffectedRows();
	}

	/**
	 * @param string $table
	 */
	public static function truncate($table) {
		$query = 'TRUNCATE TABLE `' . $table . '`';
		self::query($query);
	}

	/**
	 * @param string      $table
	 * @param string|null $column OPTIONAL
	 * @param string|null $index  OPTIONAL
	 * @return bool
	 */
	public static function exists($table, $column = null, $index = null) {
		$exists = (bool) CM_Mysql::exec("SHOW TABLES LIKE '?'", $table)->numRows();
		if ($exists && $column) {
			$exists = (bool) CM_Mysql::exec("SHOW COLUMNS FROM `?` LIKE '?'", $table, $column)->numRows();
		}
		if ($exists && $index) {
			$exists = (bool) CM_Mysql::exec("SHOW INDEX FROM `?` WHERE key_name = '?'", $table, $index, $index)->numRows();
		}
		return $exists;
	}

	/**
	 * @return int|false The number of affected rows
	 */
	public static function getAffectedRows() {
		$affectedRows = self::_getLink()->affected_rows;
		if ($affectedRows < 0) {
			return false;
		}
		return $affectedRows;
	}

	/**
	 * @return string|false Last insert query autoincrement id.
	 */
	public static function getInsertId() {
		$insertId = self::_getLink()->insert_id;
		if (!$insertId) {
			return false;
		}
		return $insertId;
	}

	/**
	 * Returns column info object
	 *
	 * @param string      $table
	 * @param string|null $column
	 * @return CM_MysqlColumn
	 */
	public static function describe($table, $column = null) {
		if ($column) {
			$result = self::query("DESCRIBE `$table` `$column`");
			return $result->fetchObject("CM_MysqlColumn");
		}

		$result = self::query("DESCRIBE `$table`");
		$columns = array();
		/** @var CM_MysqlColumn $column */
		while ($column = $result->fetchObject('CM_MysqlColumn')) {
			$columns[$column->name()] = $column;
		}
		return $columns;
	}

	/**
	 * Return a random id-guess for an id-column
	 * This id might not exist, but is within existing id-bounds
	 *
	 * @param string $table
	 * @param string $column
	 * @param string $where OPTIONAL
	 * @return int
	 */
	public static function getRandIdGuess($table, $column, $where = '1') {
		$idBounds = CM_Mysql::exec("SELECT MIN(`?`) AS min, MAX(`?`) AS max FROM `?` WHERE $where", $column, $column, $table)->fetchAssoc();
		return rand($idBounds['min'], $idBounds['max']);
	}

	/**
	 * Return an existing random id for a table
	 * If '$where' filters many rows out this might not find an id
	 *
	 * @param string $table
	 * @param string $column
	 * @param string $where OPTIONAL
	 * @return int
	 */
	public static function getRandId($table, $column, $where = '1') {
		$idGuess = self::getRandIdGuess($table, $column, $where);
		$query = "SELECT `$column`
					FROM `$table`
					WHERE $where
						AND `$column` <= $idGuess
					ORDER BY `$column` DESC
					LIMIT 1";
		$id = CM_Mysql::query($query)->fetchOne();

		if (!$id) {
			// Method above did not find an id => get any id
			$id = CM_Mysql::exec("SELECT `$column` FROM `$table` WHERE $where LIMIT 1")->fetchOne();
		}

		if (!$id) {
			throw new CM_Exception('Cannot find random id');
		}

		return $id;
	}

	public static function getColumns($table) {
		$query = self::placeholder('SHOW COLUMNS FROM `?`', $table);
		$result = CM_Mysql::query($query);

		$columns = array();
		if ($result->numRows() > 0) {
			while ($col = $result->fetchAssoc()) {
				$columns[] = $col['Field'];
			}
		}
		return $columns;
	}

	/**
	 * @param array|null  $tables
	 * @param bool|null   $skipData
	 * @param bool|null   $skipStructure
	 * @param string|null $dbName
	 * @return string
	 */
	public static function getDump(array $tables = null, $skipData = null, $skipStructure = null, $dbName = null) {
		$config = self::_getConfig();
		if (null === $dbName) {
			$dbName = $config->db;
		}
		$args = array();
		$args[] = '--compact';
		$args[] = '--add-drop-table';
		$args[] = '--extended-insert';
		if ($skipData) {
			$args[] = '--no-data';
		}
		if ($skipStructure) {
			$args[] = '--no-create-info';
		}
		$args[] = '--host=' . $config->server['host'];
		$args[] = '--port=' . $config->server['port'];
		$args[] = '--user=' . $config->user;
		$args[] = '--password=' . $config->pass;
		$args[] = $dbName;
		if ($tables) {
			foreach ($tables as $table) {
				$args[] = $table;
			}
		}

		$dump = 'SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";' . PHP_EOL;
		$dump .= '/*!40101 SET NAMES utf8 */;' . PHP_EOL;
		if (array() !== $tables) {
			$queries = CM_Util::exec('mysqldump', $args);
			$queries = preg_replace('#(\s+)AUTO_INCREMENT\s*=\s*\d+\s+#', '$1', $queries);
			$queries = preg_replace('#/\*.*?\*/;#', '', $queries);
			$dump .= $queries;
		}

		return $dump;
	}

	/**
	 * @param string  $dbName
	 * @param CM_File $dump
	 */
	public static function runDump($dbName, CM_File $dump) {
		$config = self::_getConfig();
		$args = array();
		$args[] = '--host=' . $config->server['host'];
		$args[] = '--port=' . $config->server['port'];
		$args[] = '--user=' . $config->user;
		$args[] = '--password=' . $config->pass;
		$args[] = $dbName;
		CM_Util::exec('mysql', $args, null, $dump->getPath());
	}

	/**
	 * @param bool $readOnly
	 * @return mysqli
	 */
	private static function _getLink($readOnly = false) {
		if ($readOnly) {
			return self::$_linkReadOnly ? self::$_linkReadOnly : self::connect($readOnly);
		} else {
			return self::$_link ? self::$_link : self::connect($readOnly);
		}
	}

	/**
	 * @param string|array|null $where Associative array field=>value OR string
	 * @return string WHERE-query
	 */
	private static function _queryWhere($where) {
		if (empty($where)) {
			return '';
		}
		if (is_array($where)) {
			$valuesEscaped = array();
			foreach ($where as $attr => $value) {
				if ($value === null) {
					$valuesEscaped[] = self::placeholder("`?` IS NULL", $attr);
				} else {
					$valuesEscaped[] = self::placeholder("`?`='?'", $attr, $value);
				}
			}
			$where = implode(' AND ', $valuesEscaped);
		}
		return 'WHERE ' . $where;
	}

	private static function _delayedEnabled() {
		return !IS_TEST;
	}

}
