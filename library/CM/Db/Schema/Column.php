<?php

class CM_Db_Schema_Column {

	/**
	 * @var CM_Db_Client
	 */
	private $_client;

	/**
	 * @var string
	 */
	private $_table, $_column;

	/**
	 * @var array
	 */
	private $_data;

	/**
	 * @param CM_Db_Client $client
	 * @param string       $table
	 * @param string       $column
	 */
	public function __construct(CM_Db_Client $client, $table, $column) {
		$this->_client = $client;
		$this->_table = $table;
		$this->_column = $column;
		$this->_loadData();
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

	private function _loadData() {
		if (!isset($this->_data)) {
			$query = new CM_Db_Query_Describe($this->_table, $this->_column);
			$result = $query->execute($this->_client);
			$columns = $result->fetch();
			if (false === $columns) {
				throw new CM_Db_Exception();
			}
			foreach ($columns as $var => $value) {
				if ($var === 'Type') {
					preg_match_all("/^(\\w++)(?:\\((\\d+|'[^']*(?:''[^']*)*'(?:,'[^']*(?:''[^']*)*')*)\\))?(?: (\\w++))?$/", $value, $matches);
					$this->_data['type'] = $matches[1][0];
					if (preg_match('/\\d+/', $matches[2][0])) {
						$this->_data['size'] = (int) $matches[2][0];
					} elseif (strlen($matches[2][0])) {
						preg_match_all("/,'([^']*(?:''[^']*)*)'/", ',' . $matches[2][0], $enumMatches);
						$this->_data['enum'] = array_map(function ($value) {
							return str_replace("''", "'", $value);
						}, $enumMatches[1]);
					}
					$this->_data['unsigned'] = ($matches[3][0] === 'unsigned');
				} else {
					$this->_data[$var] = $value;
				}
			}
		}
	}
}
