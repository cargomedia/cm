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
		$query = new CM_Db_Query_Delete($table, $where);
		return $query->execute(self::_getClient(false))->getAffectedRows();
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
	 * @param string|array      $fields Column-name OR Column-names array
	 * @param string|array|null $where  Associative array field=>value OR string
	 * @param string|null       $order
	 * @return CM_Db_Result
	 */
	public static function select($table, $fields, $where = null, $order = null) {
		$query = new CM_Db_Query_Select($table, $fields, $where, $order);
		return $query->execute(self::_getClient(false));
	}

	/**
	 * @param string $table
	 */
	public static function truncate($table) {
		$query = new CM_Db_Query_Truncate($table);
		$query->execute(self::_getClient(false));
	}

	/**
	 * @param string            $table
	 * @param array             $values Associative array field=>value
	 * @param string|array|null $where  Associative array field=>value OR string
	 * @return int
	 */
	public static function update($table, array $values, $where = null) {
		$query = new CM_Db_Query_Update($table, $values, $where);
		return $query->execute(self::_getClient(false))->getAffectedRows();
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
