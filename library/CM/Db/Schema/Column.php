<?php

class CM_Db_Schema_Column {

	/** @var CM_Db_Client */
	private $_client;

	/** @var string */
	private $_table;

	/** @var string */
	private $_column;

	/** @var array */
	private $_data;

	/**
	 * @param CM_Db_Client $client
	 * @param string       $table
	 * @param string       $column
	 */
	public function __construct(CM_Db_Client $client, $table, $column) {
		$this->_client = $client;
		$this->_table = (string) $table;
		$this->_column = (string) $column;
		$this->_data = $this->_getData();
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->_data['Field'];
	}

	/**
	 * @return string
	 */
	public function getType() {
		return $this->_data['type'];
	}

	/**
	 * @return int|null
	 */
	public function getSize() {
		if (!isset($this->_data['size'])) {
			return null;
		}
		return $this->_data['size'];
	}

	/**
	 * @return string[]|null
	 */
	public function getEnum() {
		if (!isset($this->_data['enum'])) {
			return null;
		}
		return $this->_data['enum'];
	}

	/**
	 * @return bool
	 */
	public function getUnsigned() {
		return $this->_data['unsigned'];
	}

	/**
	 * @return bool
	 */
	public function getAllowNull() {
		return ($this->_data['Null'] === 'YES');
	}

	/**
	 * @return string|null
	 */
	public function getDefaultValue() {
		return $this->_data['Default'];
	}

	private function _getData() {
		if (isset($this->_data)) {
			return $this->_data;
		}
		$data = array();
		$query = new CM_Db_Query_Describe($this->_client, $this->_table, $this->_column);
		$result = $query->execute();
		$columns = $result->fetch();
		if (false === $columns) {
			throw new CM_Db_Exception('Column `' . $this->_column . '` not found');
		}
		foreach ($columns as $var => $value) {
			if ($var === 'Type') {
				if (preg_match("/^(\\w++)(?:\\((\\d+|'[^']*(?:''[^']*)*'(?:,'[^']*(?:''[^']*)*')*)\\))?(?: (\\w++))?$/", $value, $matches)) {
					$data['type'] = $matches[1];
					if (isset($matches[2])) {
						if (preg_match('/\\d+/', $matches[2])) {
							$data['size'] = (int) $matches[2];
						} elseif (strlen($matches[2])) {
							preg_match_all("/,'([^']*(?:''[^']*)*)'/", ',' . $matches[2], $enumMatches);
							$data['enum'] = array_map(function ($value) {
								return str_replace("''", "'", $value);
							}, $enumMatches[1]);
						}
					}
					$data['unsigned'] = isset($matches[3]) && ($matches[3] === 'unsigned');
				}
			} else {
				$data[$var] = $value;
			}
		}
		return $data;
	}
}
