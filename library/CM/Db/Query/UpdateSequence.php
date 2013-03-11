<?php

class CM_Db_Query_UpdateSequence extends CM_Db_Query_Abstract {

	/**
	 * @param CM_Db_Client     $client
	 * @param string           $table
	 * @param string           $field
	 * @param string           $direction
	 * @param string|array     $where    Associative array field=>value OR string
	 * @param int              $lowerBound
	 * @param int              $upperBound
	 * @throws CM_Exception_Invalid
	 */
	public function __construct($client, $table, $field, $direction, array $where, $lowerBound, $upperBound) {
		parent::__construct($client);
		$this->_addSql('UPDATE ' . $this->_getClient()->quoteIdentifier($table));
		$this->_addSql('SET ' . $this->_getClient()->quoteIdentifier($field) . ' = ' . $this->_getClient()->quoteIdentifier($field) . ' + ?');
		$this->_addParameters($direction);
		$this->_addWhere($where);
		$this->_addSql('AND `?` BETWEEN ? AND ?');
		$this->_addParameters(array($field, $lowerBound, $upperBound));
	}
}
