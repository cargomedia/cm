<?php

class CM_Db_Query_SelectMultiple extends CM_Db_Query_Abstract {

	/**
	 * @param CM_Db_Client      $client
	 * @param string            $table
	 * @param string|array      $fields     Column-name OR Column-names array
	 * @param array[]           $whereList  Outer array-entries are combined using OR, inner arrays using AND
	 * @param string|array|null $order
	 */
	public function __construct(CM_Db_Client $client, $table, $fields, array $whereList, $order = null) {
		parent::__construct($client);
		$fields = (array) $fields;
		foreach ($fields as &$field) {
			if ($field == '*') {
				$field = '*';
			} else {
				$field = $this->_getClient()->quoteIdentifier($field);
			}
		}
		$this->_addSql('SELECT ' . implode(',', $fields));
		$this->_addSql('FROM ' . $this->_getClient()->quoteIdentifier($table));

		if (!empty($whereList)) {
			$whereParts = array();
			foreach ($whereList as $wherePart) {
				$sqlParts = array();
				foreach ($wherePart as $field => $value) {
					if (null === $value) {
						$sqlParts[] = $this->_getClient()->quoteIdentifier($field) . ' IS NULL';
					} else {
						$sqlParts[] = $this->_getClient()->quoteIdentifier($field) . ' = ?';
						$this->_addParameters($value);
					}
				}
				$whereParts[] = implode(' AND ', $sqlParts);
			}
			$this->_addSql('WHERE ' . implode(' OR ', $whereParts));
		}
		$this->_addOrderBy($order);
	}
}
