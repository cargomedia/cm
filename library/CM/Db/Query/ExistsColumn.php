<?php

class CM_Db_Query_ExistsColumn extends CM_Db_Query_Abstract {

	/**
	 * @param string $table
	 * @param string $column
	 */
	public function __construct($table, $column) {
		$this->_addSql('SHOW COLUMNS FROM ' . $this->_quoteIdentifier($table));
		$this->_addSql('LIKE ?');
		$this->_addParameters($column);
	}
}
