<?php

class CM_Db_Query_Count extends CM_Db_Query_Abstract {

	/**
	 * @param CM_Db_Client      $client
	 * @param string            $table
	 * @param string|array|null $where Associative array field=>value OR string
	 */
	public function __construct($client, $table, $where = null) {
		parent::__construct($client);
		$this->_addSql('SELECT COUNT(*)');
		$this->_addSql('FROM ' . $this->_getClient()->quoteIdentifier($table));
		$this->_addWhere($where);
	}
}
