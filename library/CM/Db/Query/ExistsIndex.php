<?php

class CM_Db_Query_ExistsIndex extends CM_Db_Query_Abstract {

	/**
	 * @param string $table
	 * @param string $index
	 */
	public function __construct($table, $index) {
		$this->_addSql('SHOW INDEX FROM ' . $this->_quoteIdentifier($table));
		$this->_addSql('WHERE Key_name = ?');
		$this->_addParameters($index);
	}
}
