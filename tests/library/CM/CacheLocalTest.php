<?php

class CM_CacheLocalTest extends CMTest_TestCase {

	public function testDelete() {
		try {
			CM_CacheLocal::delete('key1');
			$this->fail('Could delete key on local cache');
		} catch (CM_Exception_NotAllowed $e) {
		}
	}
}
