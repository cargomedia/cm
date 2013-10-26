<?php

class CM_Bootloader_Testing extends CM_Bootloader {

	public function __construct($pathRoot, $pathTests) {
		parent::__construct($pathRoot);
		define('DIR_TESTS', $pathTests);
		define('DIR_TEST_DATA', DIR_TESTS . 'data' . DIRECTORY_SEPARATOR);
		$this->_dataPrefix = 'test_';
	}
}
