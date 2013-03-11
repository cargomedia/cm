<?php

class CM_Db_Query_Describe extends CM_Db_Query_Abstract {

	/**
	 * @param CM_Db_Client $client
	 * @param string       $table
	 * @param string|null  $column
	 */
	public function __construct(CM_Db_Client $client, $table, $column = null) {
		parent::__construct($client);
		$this->_addSql('DESCRIBE ' . $this->_getClient()->quoteIdentifier($table));
		if (null !== $column) {
			$this->_addSql($this->_getClient()->quoteIdentifier($column));
		}
	}
}
