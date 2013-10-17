<?php

class CM_Config_Note extends CM_Class_Abstract {

	public function __get($name) {
		$this->$name = new stdClass();
		return $this->$name;
	}
}
