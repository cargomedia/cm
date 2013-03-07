<?php

class CM_Db_Query_Truncate extends CM_Db_Query_Abstract {

	/**
	 * @param string $table
	 */
	public function __construct($table) {
		$this->_addSql('TRUNCATE TABLE ' . $this->_quoteIdentifier($table));
	}
}
