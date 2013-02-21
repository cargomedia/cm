<?php

class CM_Usertext_UsertextTest extends CMTest_TestCase {

	public static function tearDownAfterClass() {
		CMTest_TH::clearEnv();
	}

	public function testProcess() {
		$usertext = new CM_Usertext_Usertext();
		$this->assertSame('foo bar',$usertext->transform('foo bar'));
	}

}
