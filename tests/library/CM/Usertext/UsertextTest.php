<?php

class CM_Usertext_UsertextTest extends CMTest_TestCase {

	public static function tearDownAfterClass() {
		CMTest_TH::clearEnv();
	}

	public function testProcess() {
		$usertext = new CM_Usertext_Usertext($this->_getRender());
		$this->assertSame('foo bar', $usertext->transform('foo bar'));
	}

	public function testCache(){
		$usertext = new CM_Usertext_Usertext($this->_getRender());
		$mode = 'oneline';
		$cacheKey = 'Usertext_Transformation_' . $mode;
		$this->assertFalse(CM_CacheLocal::get($cacheKey));
		$usertext->setMode($mode);
		$this->assertInternalType('array', CM_CacheLocal::get($cacheKey));
	}
}
