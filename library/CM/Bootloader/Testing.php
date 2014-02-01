<?php

class CM_Bootloader_Testing extends CM_Bootloader {

	public function __construct($pathRoot) {
		parent::__construct($pathRoot);
		$this->_dataPrefix = 'test_';
	}
}
