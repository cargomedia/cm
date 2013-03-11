<?php

abstract class CM_Db_Query_Abstract {

	/** @var string */
	private $_sqlTemplate = '';

	/** @var string[] */
	private $_parameters = array();

	/** @var CM_Db_Client */
	private $_client;

	/**
	 * @param CM_Db_Client $client
	 */
	public function __construct($client) {
		$this->_client = $client;
	}

	/**
	 * @return CM_Db_Client
	 */
	public function getClient() {
		return $this->_client;
	}

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
	 * @return CM_Db_Result
	 */
	public function execute() {
		$statement = $this->_client->createStatement($this->getSqlTemplate());
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
			foreach ($where as $field => $value) {
				if (null === $value) {
					$sqlParts[] = $this->_quoteIdentifier($field) . ' IS NULL';
				} else {
					$sqlParts[] = $this->_quoteIdentifier($field) . ' = ?';
					$this->_addParameters($value);
				}
			}
			$this->_addSql('WHERE ' . implode(' AND ', $sqlParts));
		} elseif (is_string($where)) {
			$this->_addSql('WHERE ' . $where);
		}
	}

	/**
	 * @param string|array|null $orderBy
	 * @throws CM_Exception_Invalid
	 */
	protected function _addOrderBy($orderBy) {
		if (null === $orderBy) {
			return;
		}
		if (!is_string($orderBy) && !is_array($orderBy)) {
			throw new CM_Exception_Invalid('Invalid order type');
		}
		if (is_array($orderBy)) {
			$sqlParts = array();
			foreach ($orderBy as $field => $direction) {
				$direction = strtoupper($direction);
				if (!is_string($field)) {
					throw new CM_Exception_Invalid('Order field name is not string');
				}
				if ('ASC' !== $direction && 'DESC' !== $direction) {
					throw new CM_Exception_Invalid('Invalid order direction `' . $direction . '`.');
				}
				$sqlParts[] = $this->_quoteIdentifier($field) . ' ' . $direction;
			}
			$this->_addSql('ORDER BY ' . implode(', ', $sqlParts));
		} elseif (is_string($orderBy)) {
			$this->_addSql('ORDER BY ' . $orderBy);
		}
	}

	/**
	 * @param string $name
	 * @return string
	 */
	protected function _quoteIdentifier($name) {
		return '`' . str_replace('`', '``', $name) . '`';
	}
}
