<?php

class CM_Db_Query_Select extends CM_Db_Query_Abstract {

	/**
	 * @param string                  $table
	 * @param string|array            $fields Column-name OR Column-names array
	 * @param string|array|null       $where  Associative array field=>value OR string
	 * @param string|array|null       $order
	 */
	public function __construct($table, $fields, $where = null, $order = null) {
		$fields = (array) $fields;
		foreach ($fields as &$field) {
			if ($field == '*') {
				$field = '*';
			} else {
				$field = $this->_quoteIdentifier($field);
			}
		}
		$this->_addSql('SELECT ' . implode(',', $fields));
		$this->_addSql('FROM ' . $this->_quoteIdentifier($table));
		$this->_addWhere($where);
		$this->_addOrderBy($order);
	}
}
