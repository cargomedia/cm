<?php

class CM_Db_Query_Insert extends CM_Db_Query_Abstract {

	/**
	 * @param string            $table
	 * @param string|array      $attr                 Column-name OR Column-names array OR associative field=>value pair
	 * @param string|array|null $value                Column-value OR Column-values array OR Multiple Column-values array(array)
	 * @param array|null        $onDuplicateKeyValues
	 * @param string            $statement
	 * @throws CM_Exception
	 */
	public function __construct($table, $attr, $value = null, array $onDuplicateKeyValues = null, $statement = 'INSERT') {
		if ($value === null && is_array($attr)) {
			$value = array_values($attr);
			$attr = array_keys($attr);
		}
		$values = (array) $value;
		$attrs = (array) $attr;
		if (!is_array(reset($values))) {
			if (count($attrs) == 1) {
				foreach ($values as &$value) {
					$value = array($value);
				}
			} else {
				$values = array($values);
			}
		}
		$attrsEscaped = array();
		foreach ($attrs as $attr) {
			$attrsEscaped[] = $this->_quoteIdentifier($attr);
		}
		$rowsEscaped = array();
		foreach ($values as &$value) {
			$row = (array) $value;
			if (count($row) != count($attrs)) {
				throw new CM_Exception('Row size does not match number of fields');
			}
			$rowEscaped = array();
			foreach ($row as $rowValue) {
				if ($rowValue === null) {
					$rowEscaped[] = 'NULL';
				} else {
					$rowEscaped[] = '?';
					$this->_addParameters($rowValue);
				}
			}
			$rowsEscaped[] = '(' . implode(',', $rowEscaped) . ')';
		}

		$this->_addSql($statement . ' INTO `' . $table . '` (' . implode(',', $attrsEscaped) . ') VALUES ' . implode(',', $rowsEscaped));
		if ($onDuplicateKeyValues) {
			$valuesEscaped = array();
			foreach ($onDuplicateKeyValues as $attr => $value) {
				if ($value === null) {
					$valuesEscaped[] = $this->_quoteIdentifier($attr) . ' = NULL';
				} else {
					$valuesEscaped[] = $this->_quoteIdentifier($attr) . ' = ?';
					$this->_addParameters($value);
				}
			}
			$this->_addSql('ON DUPLICATE KEY UPDATE ' . implode(',', $valuesEscaped));
		}
	}
}
