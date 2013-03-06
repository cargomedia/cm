<?php

class CM_Db_Query_ExistsTable extends CM_Db_Query_Abstract {

	/**
	 * @param string $table
	 */
	public function __construct($table) {
		$this->_addSql('SHOW TABLES LIKE ?');
		$this->_addParameters($table);
	}
}
