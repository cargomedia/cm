<?php

class CM_Db_Query_Truncate extends CM_Db_Query_Abstract {

	/**
	 * @param CM_Db_Client $client
	 * @param string       $table
	 */
	public function __construct(CM_Db_Client $client, $table) {
		parent::__construct($client);
		$this->_addSql('TRUNCATE TABLE ' . $this->_getClient()->quoteIdentifier($table));
	}
}
