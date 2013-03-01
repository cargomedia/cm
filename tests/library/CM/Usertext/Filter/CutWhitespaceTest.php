<?php

class CM_Usertext_Filter_CutWhitespaceTest extends CMTest_TestCase {

	public static function tearDownAfterClass() {
		CMTest_TH::clearEnv();
	}

	public function testProcess() {
		$render = $this->_getRender();
		$text = "\n\n \t   foo  \nbar     \n \r \t";
		$expected = "foo\nbar";
		$filter = new CM_Usertext_Filter_CutWhitespace();
		$actual = $filter->transform($text, $render);

		$this->assertSame($expected, $actual);
	}
}
