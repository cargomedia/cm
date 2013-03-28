<?php

class CM_Usertext_Filter_Markdown_UnescapeBlockquoteTest extends CMTest_TestCase {

	public function tearDown() {
		CMTest_TH::clearEnv();
	}

	public function testProcess() {
		$text = <<<'EOD'
&gt; blockquote
    &gt; blockquote
EOD;
		$expected = <<<'EOD'
> blockquote
    > blockquote
EOD;
		$filter = new CM_Usertext_Filter_Markdown_UnescapeBlockquote();
		$actual = $filter->transform($text, $this->_getRender());

		$this->assertSame($expected, $actual);
	}
}
