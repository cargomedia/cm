<?php

class CM_Usertext_Filter_MarkdownTest extends CMTest_TestCase {

	public static function tearDownAfterClass() {
		CMTest_TH::clearEnv();
	}

	public function testProcess() {
		$text = "#Headline#\n * element1\n * element1\n\nparagraph\nLink: [google.com](http://www.google.com)\ntest2";
		$expected = "<h1>Headline</h1>\n<ul>\n<li>element1</li>\n<li>element1</li>\n</ul>\n<p>paragraph</p>\n<p>Link: <a href=\"http://www.google.com\">google.com</a></p>\n<p>test2</p>\n";
		$filter = new CM_Usertext_Filter_Markdown();
		$actual = $filter->transform($text, $this->_getRender());

		$this->assertSame($expected, $actual);
	}

	public function testProcessWithoutLinks() {
		$text = "#Headline#\n * element1\n * element1\n\nparagraph\nLink: [google.com](http://www.google.com)\ntest2";
		$expected = "<h1>Headline</h1>\n<ul>\n<li>element1</li>\n<li>element1</li>\n</ul>\n<p>paragraph</p>\n<p>Link: google.com</p>\n<p>test2</p>\n";
		$filter = new CM_Usertext_Filter_Markdown(true);
		$actual = $filter->transform($text, $this->_getRender());

		$this->assertSame($expected, $actual);
	}
}
