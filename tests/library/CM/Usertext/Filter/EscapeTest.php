<?php

class CM_Usertext_Filter_EscapeTest extends CMTest_TestCase {

	public static function tearDownAfterClass() {
		CMTest_TH::clearEnv();
	}

	public function testProcess() {
		$text = '<b>foo</b> <script></script> <strong>bar</strong>';
		$expected = '&lt;b>foo&lt;/b> &lt;script>&lt;/script> &lt;strong>bar&lt;/strong>';
		$filter = new CM_Usertext_Filter_Escape();
		$actual = $filter->transform($text, $this->_getRender());

		$this->assertSame($expected, $actual);
	}

	public function testMultibyte() {
		$expected = '繁體字';
		$filter = new CM_Usertext_Filter_Escape();
		$actual = $filter->transform('繁體字', $this->_getRender());
		$this->assertSame($expected, $actual);
	}
}
