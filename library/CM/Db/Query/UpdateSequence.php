<?php

class CM_Db_Query_UpdateSequence extends CM_Db_Query_Abstract {

	/**
	 * @param CM_Db_Client $client
	 * @param string       $table
	 * @param string       $field
	 * @param int          $direction
	 * @param array|null   $where Associative array field=>value
	 * @param int          $lowerBound
	 * @param int          $upperBound
	 */
	public function __construct($client, $table, $field, $direction, $where = null, $lowerBound, $upperBound) {
		parent::__construct($client);
		$this->_addSql('UPDATE ' . $this->_getClient()->quoteIdentifier($table));
		$this->_addSql('SET ' . $this->_getClient()->quoteIdentifier($field) . ' = ' . $this->_getClient()->quoteIdentifier($field) . ' + ?');
		$this->_addParameters($direction);
		$this->_addWhere($where);
		$combinationStatement = ($where) ? 'AND' : 'WHERE';
		$this->_addSql($combinationStatement . ' ' . $this->_getClient()->quoteIdentifier($field) . ' BETWEEN ? AND ?');
		$this->_addParameters(array($lowerBound, $upperBound));
	}
}
