<?php

class CM_Db_Db extends CM_Class_Abstract {

	const STMT_INSERT = 'INSERT';
	const STMT_INSERT_IGNORE = 'INSERT IGNORE';
	const STMT_INSERT_DELAYED = 'INSERT DELAYED';
	const STMT_REPLACE = 'REPLACE';
	const STMT_REPLACE_DELAYED = 'REPLACE DELAYED';

	/** @var CM_Db_Client */
	private static $_client;

	/** @var CM_Db_Client */
	private static $_clientReadOnly;

	/**
	 * @param string $query
	 * @param mixed  $arg1,...
	 * @return CM_MysqlResult|int|string|bool Mysql-result, last insert id, affected rows or FALSE if none affected
	 */
	public static function exec($query) {
		$client = self::_getClient(false);
		$query = call_user_func_array(array('self', 'placeholder'), func_get_args());
		$result = $client->query($query);
		if ($result instanceof CM_MysqlResult) {
			return $result;
		} elseif ($insertId = $client->getInsertId()) {
			return $insertId;
		} else {
			return $client->getAffectedRows();
		}
	}

	/**
	 * Select fields from a table
	 *
	 * @param string            $table
	 * @param string|array      $attrs Column-name OR Column-names array
	 * @param string|array|null $where Associative array field=>value OR string
	 * @param string|null       $order
	 * @return CM_MysqlResult|bool
	 */
	public static function select($table, $attrs, $where = null, $order = null) {
		$client = self::_getClient(false);
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
		return $client->query($query);
	}

	/**
	 * Insert one/multiple rows
	 *
	 * @param string            $table
	 * @param string|array      $attr           Column-name OR Column-names array OR associative field=>value pair
	 * @param string|array|null $value          Column-value OR Column-values array OR Multiple Column-values array(array)
	 * @param array|null        $onDuplicateKeyValues
	 * @param string            $statement
	 * @throws CM_Exception
	 * @return int|string Insert Id
	 */
	public static function insert($table, $attr, $value = null, array $onDuplicateKeyValues = null, $statement = self::STMT_INSERT) {
		$client = self::_getClient(false);
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
		$client->query($query);
		return $client->getInsertId();
	}

	/**
	 * @param string $queryTemplate
	 * @param mixed  $arg,...
	 * @return string
	 */
	public static function placeholder($queryTemplate) {
		$client = self::_getClient(false);
		$arguments = func_get_args();
		$c_query = array_shift($arguments);
		if (!is_array($c_query)) {
			$c_query = self::_placeholderCompile($c_query);
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
							$var = $client->escapeString($var);
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
						$query .= $client->escapeString($var);
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
	 * @param bool $readOnly
	 * @throws CM_Exception_Invalid
	 * @return CM_Db_Client
	 */
	private static function _getClient($readOnly) {
		if ($readOnly) {
			$client = & self::$_clientReadOnly;
		} else {
			$client = & self::$_client;
		}
		if (!$client) {
			$config = self::_getConfig();
			if ($readOnly && $config->serversReadEnabled) {
				if (empty($config->serversRead)) {
					throw new CM_Exception_Invalid('No read servers configured');
				}
				$server = $config->serversRead[array_rand($config->serversRead)];
			} else {
				$server = $config->server;
			}
			$client = new CM_Db_Client($server['host'], $server['port'], $config->username, $config->password, $config->db);
		}
		return $client;
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

	/**
	 * @param string $queryTemplate
	 * @return string
	 */
	private static function _placeholderCompile($queryTemplate) {
		// Replace TBL_* constants
		$queryTemplate = preg_replace_callback('/(TBL_.+?)\b/', function ($matches) {
			return '`' . constant($matches[1]) . '`';
		}, $queryTemplate);

		$compiled = array();
		$i = 0; // placeholders counter
		$p = 0; // current position
		$prev_p = 0; // previous position

		while (false !== ($p = strpos($queryTemplate, '?', $p))) {
			$compiled[] = substr($queryTemplate, $prev_p, $p - $prev_p);

			$type_char = $char = $queryTemplate{$p - 1};

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

			$next_char = isset($queryTemplate{$p + 1}) ? $queryTemplate{$p + 1} : null;
			if ($next_char === '@') { // array list
				$compiled[] = array($i++, $type, '@');
				$prev_p = ($p = $p + 2);
			} else {
				$compiled[] = array($i++, $type);
				$prev_p = ($p = $p + 1);
			}
		}

		$tail_length = (strlen($queryTemplate) - $prev_p);
		if ($tail_length) {
			$compiled[] = substr($queryTemplate, -$tail_length);
		}

		return $compiled;
	}

}
