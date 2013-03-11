<?php

class CM_Db_Query_Describe extends CM_Db_Query_Abstract {

	/**
	 * @param string      $table
	 * @param string|null $column
	 */
	public function __construct($table, $column = null) {
		$this->_addSql('DESCRIBE ' . $this->_quoteIdentifier($table));
		if (null !== $column) {
			$this->_addSql($this->_quoteIdentifier($column));
		}
	}
}
