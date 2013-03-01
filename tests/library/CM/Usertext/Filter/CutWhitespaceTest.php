<?php

class CM_Usertext_Filter_CutWhitespaceTest extends CMTest_TestCase {

	public static function tearDownAfterClass() {
		CMTest_TH::clearEnv();
	}

	public function testProcess() {
		$text = "\n\n \t   foo  \nbar     \n \r \t";
		$expected = "foo\nbar";
		$filter = new CM_Usertext_Filter_CutWhitespace();
		$actual = $filter->transform($text, $this->_getRender());

		$this->assertSame($expected, $actual);
	}
}
