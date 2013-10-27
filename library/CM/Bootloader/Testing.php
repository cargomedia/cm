<?php

class CM_Bootloader_Testing extends CM_Bootloader {

	public function __construct($pathRoot, $pathTests) {
		parent::__construct($pathRoot);
		define('DIR_TESTS', $pathTests);
		define('DIR_TEST_DATA', DIR_TESTS . 'data/');
		$this->_dataPrefix = 'test_';
	}

	protected function _constants() {
		define('DIR_DATA', DIR_TMP . 'tests/data/');
		define('DIR_USERFILES', DIR_TMP . 'tmp/userfiles');
		parent::_constants();
	}
}
