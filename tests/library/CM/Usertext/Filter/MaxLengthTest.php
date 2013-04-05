<?php

class CM_Usertext_Filter_MaxLengthTest extends CMTest_TestCase {

	public static function tearDownAfterClass() {
		CMTest_TH::clearEnv();
	}

	public function testProcess() {
		$filter = new CM_Usertext_Filter_MaxLength(10);
		$this->assertSame('Hello…', $filter->transform('Hello World', $this->_getRender()));

		$filter = new CM_Usertext_Filter_MaxLength(11);
		$this->assertSame('Hello World', $filter->transform('Hello World', $this->_getRender()));

		$filter = new CM_Usertext_Filter_MaxLength(12);
		$this->assertSame('Hello World', $filter->transform('Hello World', $this->_getRender()));
	}

	public function testMultibyte() {
		$filter = new CM_Usertext_Filter_MaxLength(2);
		$this->assertSame('繁體…', $filter->transform('繁體字', $this->_getRender()));
	}
}
