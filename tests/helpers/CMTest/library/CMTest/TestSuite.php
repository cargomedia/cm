<?php

class CMTest_TestSuite extends PHPUnit_Framework_TestSuite {

	/**
	 * @param string $dirTestsData
	 */
	public function setDirTestData($dirTestsData) {
		define('DIR_TEST_DATA', $dirTestsData);
	}

	public function bootstrap() {
		CMTest_TH::init();
		register_shutdown_function(array($this, 'cleanup'));
	}

	public function cleanup() {
		CMTest_TH::clearEnv();
		CM_Util::rmDir(CM_Bootloader::getInstance()->getDirData());
		CM_Util::rmDir(CM_Bootloader::getInstance()->getDirUserfiles());
		CM_Db_Db::getClient(false)->disconnect();
	}

	protected function tearDown() {
		CM_Db_Db::getClient(false)->disconnect();
	}
}
