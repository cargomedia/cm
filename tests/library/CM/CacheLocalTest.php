<?php
require_once __DIR__ . '/../../TestCase.php';

class CM_CacheLocalTest extends TestCase {

	public static function setUpBeforeClass() {
	}

	public static function tearDownAfterClass() {
		TH::clearEnv();
	}

	public function testDelete() {
		try {
			CM_CacheLocal::delete('key1');
			$this->fail('Could delete key on local cache');
		} catch(CM_Exception_NotAllowed $e) {
		}
	}
}
