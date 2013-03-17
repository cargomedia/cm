<?php

class CM_Db_Query_Insert extends CM_Db_Query_Abstract {

	/**
	 * @param CM_Db_Client      $client
	 * @param string            $table
	 * @param string|array      $fields                 Column-name OR Column-names array OR associative field=>value pair
	 * @param string|array|null $values                 Column-value OR Column-values array OR Multiple Column-values array(array)
	 * @param array|null        $onDuplicateKeyValues
	 * @param string            $statement
	 * @throws CM_Exception
	 */
	public function __construct(CM_Db_Client $client, $table, $fields, $values = null, array $onDuplicateKeyValues = null, $statement = 'INSERT') {
		parent::__construct($client);
		if ($values === null && is_array($fields)) {
			$values = array_values($fields);
			$fields = array_keys($fields);
		}
		$valueList = (array) $values;
		$fieldList = (array) $fields;
		if (!is_array(reset($valueList))) {
			if (count($fieldList) == 1) {
				foreach ($valueList as &$values) {
					$values = array($values);
				}
			} else {
				$valueList = array($valueList);
			}
		}
		foreach ($fieldList as &$fields) {
			$fields = $this->_getClient()->quoteIdentifier($fields);
		}
		$rowsList = array();
		foreach ($valueList as &$values) {
			$row = (array) $values;
			if (count($row) != count($fieldList)) {
				throw new CM_Exception('Row size does not match number of fields');
			}
			$rowList = array();
			foreach ($row as $rowValue) {
				if ($rowValue === null) {
					$rowList[] = 'NULL';
				} else {
					$rowList[] = '?';
					$this->_addParameters($rowValue);
				}
			}
			$rowsList[] = '(' . implode(',', $rowList) . ')';
		}

		$this->_addSql($statement . ' INTO ' . $this->_getClient()->quoteIdentifier($table));
		$this->_addSql('(' . implode(',', $fieldList) . ')');
		$this->_addSql('VALUES ' . implode(',', $rowsList));

		if ($onDuplicateKeyValues) {
			$valuesList = array();
			foreach ($onDuplicateKeyValues as $fields => $values) {
				if ($values === null) {
					$valuesList[] = $this->_getClient()->quoteIdentifier($fields) . ' = NULL';
				} else {
					$valuesList[] = $this->_getClient()->quoteIdentifier($fields) . ' = ?';
					$this->_addParameters($values);
				}
			}

			$this->_addSql('ON DUPLICATE KEY UPDATE ' . implode(',', $valuesList));
		}
	}
}
