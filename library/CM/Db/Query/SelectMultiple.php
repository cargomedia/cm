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
			$wherePartCommon = array();
			if (count($whereList) >= 2) {
				$wherePartCommon = reset($whereList);
				foreach ($whereList as $wherePart) {
					$wherePartCommon = array_uintersect_assoc($wherePartCommon, $wherePart,
						function ($a, $b) {
							return $a !== $b;
						});
				}
			}
			$whereParts = array();
			foreach ($whereList as $wherePart) {
				$sqlParts = array();
				$wherePartFiltered = array_udiff_assoc($wherePart, $wherePartCommon,
					function ($a, $b) {
						return $a !== $b;
					});
				if (!empty($wherePartFiltered)) {
					foreach ($wherePartFiltered as $field => $value) {
						if (null === $value) {
							$sqlParts[] = $this->_getClient()->quoteIdentifier($field) . ' IS NULL';
						} else {
							$sqlParts[] = $this->_getClient()->quoteIdentifier($field) . ' = ?';
							$this->_addParameters($value);
						}
					}
					$whereParts[] = implode(' AND ', $sqlParts);
				}
			}
			if (empty($wherePartCommon)) {
				$this->_addSql('WHERE ' . implode(' OR ', $whereParts));
			} else {
				if (!empty($whereParts)) {
					$this->_addSql('WHERE ' . '(' . implode(' OR ', $whereParts) . ') AND' . '');
				} else {
					$this->_addSql('WHERE');
				}
				$sqlParts = array();
				foreach ($wherePartCommon as $field => $value) {
					if (null === $value) {
						$sqlParts[] = $this->_getClient()->quoteIdentifier($field) . ' IS NULL';
					} else {
						$sqlParts[] = $this->_getClient()->quoteIdentifier($field) . ' = ?';
						$this->_addParameters($value);
					}
				}
				$this->_addSql(implode(' AND ', $sqlParts));
			}
		}
		$this->_addOrderBy($order);
	}
}
