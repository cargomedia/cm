<?php

class CM_Usertext_Filter_StriptagsTest extends CMTest_TestCase {

	public function testProcess() {
		$text = "<p>foo<br><br/></p>";

		$filter = new CM_Usertext_Filter_Striptags();
		$this->assertSame('foo', $filter->transform($text, new CM_Render()));

		$filter = new CM_Usertext_Filter_Striptags(array('p'));
		$this->assertSame('<p>foo</p>', $filter->transform($text, new CM_Render()));
	}
}
