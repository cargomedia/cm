<?php

class CM_Usertext_Filter_Emoticon_EscapeMarkdownTest extends CMTest_TestCase {

	public function tearDown() {
		CMTest_TH::clearEnv();
	}

	public function testProcess() {
		$text = 'foo_bar foo___bar__foo :foo_bar: _foo_ :foo_bar_foo:';
		$expected = 'foo_bar foo___bar__foo :foo-bar: _foo_ :foo-bar-foo:';
		$filter = new CM_Usertext_Filter_Emoticon_EscapeMarkdown();
		$actual = $filter->transform($text);

		$this->assertSame($expected, $actual);
	}
}
