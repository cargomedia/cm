<?php

class CM_Usertext_Filter_EscapeTest extends CMTest_TestCase {

	public function testProcess() {
		$text = "<b>foo</b> <script></script> <strong>bar</strong>";
		$expected = "&lt;b&gt;foo&lt;/b&gt; &lt;script&gt;&lt;/script&gt; &lt;strong&gt;bar&lt;/strong&gt;";
		$filter = new CM_Usertext_Filter_Escape();
		$actual = $filter->transform($text, new CM_Render());

		$this->assertSame($expected, $actual);
	}

	public function testMultibyte() {
		$expected = '繁體字';
		$filter = new CM_Usertext_Filter_Escape();
		$actual = $filter->transform('繁體字', new CM_Render());
		$this->assertSame($expected, $actual);
	}
}
