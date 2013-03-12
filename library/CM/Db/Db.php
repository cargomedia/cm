<?php

class CM_Db_Db extends CM_Class_Abstract {

	/** @var CM_Db_Client */
	private static $_client;

	/** @var CM_Db_Client */
	private static $_clientReadOnly;

	/**
	 * @param bool $readOnly
	 * @throws CM_Db_Exception
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
					throw new CM_Db_Exception('No read servers configured');
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
	 * @param string            $table
	 * @param string|array|null $where Associative array field=>value OR string
	 * @return int
	 */
	public static function count($table, $where = null) {
		$client = self::_getClient(false);
		$query = new CM_Db_Query_Count($client, $table, $where);
		return $query->execute()->fetchColumn();
	}

	/**
	 * @param string            $table
	 * @param string|array|null $where
	 * @return int
	 */
	public static function delete($table, $where = null) {
		$client = self::_getClient(false);
		$query = new CM_Db_Query_Delete($client, $table, $where);
		return $query->execute()->getAffectedRows();
	}

	/**
	 * @param string     $sqlTemplate
	 * @param array|null $parameters
	 * @return CM_Db_Result
	 */
	public static function exec($sqlTemplate, array $parameters = null) {
		$sqlTemplate = self::_replaceTableConsts($sqlTemplate);
		$client = self::_getClient(false);
		return $client->createStatement($sqlTemplate)->execute($parameters);
	}

	/**
	 * @param string     $sqlTemplate
	 * @param array|null $parameters
	 * @return CM_Db_Result
	 */
	public static function execRead($sqlTemplate, array $parameters = null) {
		$sqlTemplate = self::_replaceTableConsts($sqlTemplate);
		$client = self::_getClient(true);
		return $client->createStatement($sqlTemplate)->execute($parameters);
	}

	/**
	 * @param string            $table
	 * @param string|array      $fields               Column-name OR Column-names array OR associative field=>value pair
	 * @param string|array|null $values               Column-value OR Column-values array OR Multiple Column-values array(array)
	 * @param array|null        $onDuplicateKeyValues
	 * @param string|null       $statement
	 * @return string|null
	 */
	public static function insert($table, $fields, $values = null, array $onDuplicateKeyValues = null, $statement = null) {
		if (null === $statement) {
			$statement = 'INSERT';
		}
		$client = self::_getClient(false);
		$query = new CM_Db_Query_Insert($client, $table, $fields, $values, $onDuplicateKeyValues, $statement);
		$query->execute();
		return $client->getLastInsertId();
	}

	/**
	 * @param string            $table
	 * @param string|array      $fields
	 * @param string|array|null $values
	 * @return string|null
	 */
	public static function insertIgnore($table, $fields, $values = null) {
		return self::insert($table, $fields, $values, null, 'INSERT IGNORE');
	}

	/**
	 * @param string            $table
	 * @param string|array      $fields Column-name OR Column-names array OR associative field=>value pair
	 * @param string|array|null $values Column-value OR Column-values array OR Multiple Column-values array(array)
	 * @param array|null        $onDuplicateKeyValues
	 * @return string|null
	 */
	public static function insertDelayed($table, $fields, $values = null, array $onDuplicateKeyValues = null) {
		$statement = (self::_getConfig()->delayedEnabled) ? 'INSERT DELAYED' : 'INSERT';
		return self::insert($table, $fields, $values, $onDuplicateKeyValues, $statement);
	}

	/**
	 * @param string            $table
	 * @param string|array      $fields Column-name OR Column-names array OR associative field=>value pair
	 * @param string|array|null $values Column-value OR Column-values array OR Multiple Column-values array(array)
	 * @return string|null
	 */
	public static function replace($table, $fields, $values = null) {
		return self::insert($table, $fields, $values, null, 'REPLACE');
	}

	/**
	 * @param string            $table
	 * @param string|array      $fields Column-name OR Column-names array OR associative field=>value pair
	 * @param string|array|null $values Column-value OR Column-values array OR Multiple Column-values array(array)
	 * @return string|null
	 */
	public static function replaceDelayed($table, $fields, $values = null) {
		$statement = (self::_getConfig()->delayedEnabled) ? 'REPLACE DELAYED' : 'REPLACE';
		return self::insert($table, $fields, $values, null, $statement);
	}

	/**
	 * @param string            $table
	 * @param string|array      $fields Column-name OR Column-names array
	 * @param string|array|null $where  Associative array field=>value OR string
	 * @param string|null       $order
	 * @return CM_Db_Result
	 */
	public static function select($table, $fields, $where = null, $order = null) {
		$client = self::_getClient(false);
		$query = new CM_Db_Query_Select($client, $table, $fields, $where, $order);
		return $query->execute();
	}

	/**
	 * @param string $table
	 */
	public static function truncate($table) {
		$client = self::_getClient(false);
		$query = new CM_Db_Query_Truncate($client, $table);
		$query->execute();
	}

	/**
	 * @param string            $table
	 * @param array             $values Associative array field=>value
	 * @param string|array|null $where  Associative array field=>value OR string
	 * @return int
	 */
	public static function update($table, array $values, $where = null) {
		$client = self::_getClient(false);
		$query = new CM_Db_Query_Update($client, $table, $values, $where);
		return $query->execute()->getAffectedRows();
	}

	/**
	 * @param string     $table
	 * @param array      $update   Associative array field=>value
	 * @param array      $whereRow Associative array field=>value
	 * @param array|null $where    Associative array field=>value
	 * @throws CM_Exception_Invalid
	 */
	public static function updateSequence($table, $update, array $whereRow, array $where = null) {
		if (1 < count($update)) {
			throw new CM_Exception_Invalid('Only one column can be updated.');
		}
		if (null !== $where) {
			$where = (array) $where;
		}
		$value = (int) reset($update);
		$field = key($update);

		if ($value <= 0 || $value > CM_Db_Db::count($table, $where)) {
			throw new CM_Exception_Invalid('Sequence out of bounds.');
		}

		$whereMerged = is_array($where) ? array_merge($whereRow, $where) : $whereRow;
		$valueOld = CM_Db_Db::select($table, $field, $whereMerged)->fetchColumn();
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

		$client = self::_getClient(false);
		$query = new CM_Db_Query_UpdateSequence($client, $table, $field, $direction, $where, $lowerBound, $upperBound);
		$query->execute();

		self::update($table, array($field => $value), $whereMerged);
	}

	/**
	 * @param string      $table
	 * @param string      $column
	 * @param string|null $where
	 * @return int
	 * @throws CM_DB_Exception
	 */
	public static function getRandId($table, $column, $where = null) {
		$client = self::_getClient(false);
		$idGuess = self::_getRandIdGuess($table, $column, $where);
		$columnQuoted = $client->quoteIdentifier($column);
		$whereGuessId = (null === $where ? '' : $where . ' AND ') . $columnQuoted . " <= $idGuess";
		$id = CM_Db_Db::select($table, $column, $whereGuessId)->fetchColumn();

		if (!$id) {
			$id = CM_Db_Db::select($table, $column, $where)->fetchColumn();
		}
		if (!$id) {
			throw new CM_Db_Exception('Cannot find random id');
		}
		return $id;
	}

	/**
	 * @param string      $table
	 * @param string      $column
	 * @param string|null $where
	 * @return int
	 */
	private static function _getRandIdGuess($table, $column, $where = null) {
		$client = self::_getClient(false);
		$columnQuoted = $client->quoteIdentifier($column);
		$sql = 'SELECT MIN(' . $columnQuoted . ') AS min, MAX(' . $columnQuoted . ') AS max FROM ' . $client->quoteIdentifier($table);
		if (null !== $where) {
			$sql .= ' WHERE ' . (string) $where;
		}
		$idBounds = CM_Db_Db::exec($sql)->fetch();
		return rand($idBounds['min'], $idBounds['max']);
	}

	/**
	 * @param string $query
	 * @return string
	 */
	private static function _replaceTableConsts($query) {
		return preg_replace_callback('/(TBL_.+?)\b/', function ($matches) {
			return '`' . constant($matches[1]) . '`';
		}, $query);
	}
}
