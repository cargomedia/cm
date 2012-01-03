<?php

class CM_MysqlColumn {
	private $column_info = array();

	public function __construct() {
	}

	public function __set($var, $value) {
		if ($var == 'Type') {
			$matches = array();
			preg_match_all('/^(\w+)(\(\S+\))?\s?(\w+)?$/', $value, $matches);
			$this->column_info['type'] = $matches[1][0];
			$this->column_info['size'] = str_replace("'", "", trim($matches[2][0], '()'));
			$this->column_info['unsigned'] = ($matches[3][0] == 'unsigned');
		} else {
			$this->column_info[$var] = $value;
		}
	}

	public function size() {
		return $this->column_info['size'];
	}

	public function enums() {
		if ($this->type() != 'enum') {
			return array();
		}
		return explode(',', $this->size());
	}

	public function type() {
		return $this->column_info['type'];
	}

	public function unsigned() {
		return (bool) $this->column_info['unsigned'];
	}

	public function allowNull() {
		return (bool) ($this->column_info['Null'] == "YES");
	}

	public function defaultValue() {
		return (bool) $this->column_info['Default'];
	}

	public function name() {
		return $this->column_info['Field'];
	}

	public function is_string() {
		switch ($this->column_info['type']) {
			case "varchar":
			case "char":
			case "text":
			case "enum":
			case "date":
				return true;
			default:
				return false;
		}
	}

}
