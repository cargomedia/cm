<?php

class CM_Db_Query_Count extends CM_Db_Query_Abstract {

	/**
	 * @param string            $table
	 * @param string|array|null $where  Associative array field=>value OR string
	 */
	public function __construct($table, $where = null) {
		$this->_addSql('SELECT COUNT(*) FROM ' . $this->_quoteIdentifier($table));
		$this->_addWhere($where);
	}
}
