<?php

class CM_Db_Query_Select extends CM_Db_Query_Abstract {

	/**
	 * @param CM_Db_Client      $client
	 * @param string            $table
	 * @param string|array      $fields Column-name OR Column-names array
	 * @param string|array|null $where  Associative array field=>value OR string
	 * @param string|array|null $order
	 */
	public function __construct(CM_Db_Client $client, $table, $fields, $where = null, $order = null) {
		parent::__construct($client);
		$fields = (array) $fields;
		foreach ($fields as &$field) {
			if ($field == '*') {
				$field = '*';
			} else {
				$field = $this->_getClient()->quoteIdentifier($field);
			}
		}
		$this->_addSql('SELECT ' . implode(',', $fields));
		$this->_addSql('FROM ' . $this->_getClient()->quoteIdentifier($table));
		$this->_addWhere($where);
		$this->_addOrderBy($order);
	}
}
