<?php

class CM_Db_Query_Update extends CM_Db_Query_Abstract {

	/**
	 * @param string            $table
	 * @param string|array      $values Associative array field=>value
	 * @param string|array|null $where  Associative array field=>value OR string
	 */
	public function __construct($table, array $values, $where = null) {
		$sqlParts = array();
		foreach ($values as $field => $value) {
			if (null === $value) {
				$sqlParts[] = $this->_quoteIdentifier($field) . ' = NULL';
			} else {
				$sqlParts[] = $this->_quoteIdentifier($field) . ' = ?';
				$this->_addParameters($value);
			}
		}

		$this->_addSql('UPDATE ' . $this->_quoteIdentifier($table));
		$this->_addSql('SET ' . implode(', ', $sqlParts));
		$this->_addWhere($where);
	}
}
