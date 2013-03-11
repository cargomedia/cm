<?php

class CM_Db_Query_Delete extends CM_Db_Query_Abstract {

	/**
	 * @param CM_Db_Client      $client
	 * @param string            $table
	 * @param string|array|null $where  Associative array field=>value OR string
	 */
	public function __construct(CM_Db_Client $client, $table, $where = null) {
		parent::__construct($client);
		$this->_addSql('DELETE FROM ' . $this->_getClient()->quoteIdentifier($table));
		$this->_addWhere($where);
	}
}
