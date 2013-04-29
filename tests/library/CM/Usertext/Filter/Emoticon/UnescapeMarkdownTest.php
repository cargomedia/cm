<?php

class CM_Usertext_Filter_Emoticon_UnescapeMarkdownTest extends CMTest_TestCase {

	public function tearDown() {
		CMTest_TH::clearEnv();
	}

	public function testProcess() {
		$text = 'foo-bar foo---bar--foo :foo-bar: -foo- :foo-bar-foo:';
		$expected = 'foo-bar foo---bar--foo :foo_bar: -foo- :foo_bar_foo:';
		$filter = new CM_Usertext_Filter_Emoticon_UnescapeMarkdown();
		$actual = $filter->transform($text, new CM_Render());

		$this->assertSame($expected, $actual);
	}
}
