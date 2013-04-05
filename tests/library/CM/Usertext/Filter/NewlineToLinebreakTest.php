<?php

class CM_Usertext_Filter_NewlineToLinebreakTest extends CMTest_TestCase {

	public static function tearDownAfterClass() {
		CMTest_TH::clearEnv();
	}

	public function testProcess() {
		$text = "foo\n\rbar\n\n\n\n\n\n\n\n\nfoo\n\nbar\n\r\r\r";
		$expected = "foo<br />\nbar<br />\n<br />\n<br />\nfoo<br />\n<br />\nbar";
		$filter = new CM_Usertext_Filter_NewlineToLinebreak(3);

		$this->assertSame($expected, $filter->transform($text, $this->_getRender()));
	}
}
