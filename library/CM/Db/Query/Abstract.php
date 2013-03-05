<?php

abstract class CM_Db_Query_Abstract {

	/** @var string */
	private $_sqlTemplate = '';

	/** @var string[] */
	private $_parameters = array();

	/**
	 * @return string
	 */
	public function getSqlTemplate() {
		return $this->_sqlTemplate;
	}

	/**
	 * @return string[]
	 */
	public function getParameters() {
		return $this->_parameters;
	}

	/**
	 * @param CM_Db_Client $client
	 * @return CM_Db_Result
	 */
	public function execute(CM_Db_Client $client) {
		$statement = $client->createStatement($this->getSqlTemplate());
		return $statement->execute($this->getParameters());
	}

	/**
	 * @param string $sql
	 */
	protected function _addSql($sql) {
		if (!empty($this->_sqlTemplate)) {
			$this->_sqlTemplate .= ' ';
		}
		$this->_sqlTemplate .= (string) $sql;
	}

	/**
	 * @param string[]|string
	 */
	protected function _addParameters($parameters) {
		if (is_array($parameters)) {
			$this->_parameters = array_merge($this->_parameters, $parameters);
		} else {
			array_push($this->_parameters, $parameters);
		}
	}

	/**
	 * @param string|array|null $where
	 * @throws CM_Exception_Invalid
	 */
	protected function _addWhere($where) {
		if (null === $where) {
			return;
		}
		if (!is_string($where) && !is_array($where)) {
			throw new CM_Exception_Invalid('Invalid where type');
		}
		if (is_array($where)) {
			$sqlParts = array();
			foreach ($where as $attr => $value) {
				if (null === $value) {
					$sqlParts[] = $this->_quoteIdentifier($attr) . ' IS NULL';
				} else {
					$sqlParts[] = $this->_quoteIdentifier($attr) . ' = ?';
					$this->_addParameters($value);
				}
			}
			$this->_addSql('WHERE ' . implode(' AND ', $sqlParts));
		} elseif (is_string($where)) {
			$this->_addSql('WHERE ' . $where);
		}
	}

	/**
	 * @param string|null $orderBy
	 * @throws CM_Exception_Invalid
	 */
	protected function _addOrderBy($orderBy) {
		if (!is_string($orderBy) && !is_null($orderBy)) {
			throw new CM_Exception_Invalid('Invalid order type');
		}
		if (null === $orderBy) {
			return;
		}
		$this->_addSql('ORDER BY ' . $orderBy);
	}

	/**
	 * @param string $name
	 * @return string
	 */
	protected function _quoteIdentifier($name) {
		return '`' . str_replace('`', '``', $name) . '`';
	}
}
