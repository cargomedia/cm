<?php

class CM_CacheLocalTest extends CMTest_TestCase {

	public static function setUpBeforeClass() {
	}

	public static function tearDownAfterClass() {
		CMTest_TH::clearEnv();
	}

	public function testDelete() {
		try {
			CM_CacheLocal::delete('key1');
			$this->fail('Could delete key on local cache');
		} catch(CM_Exception_NotAllowed $e) {
		}
	}
}
