<?php

class CM_Db_Query_UpdateSequence extends CM_Db_Query_Abstract {

	/**
	 * @param string           $table
	 * @param string           $field
	 * @param string           $direction
	 * @param string|array     $where    Associative array field=>value OR string
	 * @param int              $lowerBound
	 * @param int              $upperBound
	 * @throws CM_Exception_Invalid
	 */
	public function __construct($table, $field, $direction, array $where, $lowerBound, $upperBound) {
		$this->_addSql('UPDATE ' . $this->_quoteIdentifier($table));
		$this->_addSql('SET ' . $this->_quoteIdentifier($field) . ' = ' . $this->_quoteIdentifier($field) . ' + ?');
		$this->_addParameters($direction);
		$this->_addWhere($where);
		$this->_addSql('AND `?` BETWEEN ? AND ?');
		$this->_addParameters(array($field, $lowerBound, $upperBound));
	}
}
