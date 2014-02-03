<?php

class CM_Config_Node {

	public function __get($name) {
		return $this->$name = new self();
	}

	/**
	 * @return stdClass
	 */
	public function export() {
		$object = new stdClass();
		foreach (get_object_vars($this) as $key => $value) {
			if ($value instanceof self) {
				$object->$key = $value->export();
			} else {
				$object->$key = $value;
			}
		}
		return $object;
	}
}
