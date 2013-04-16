<?php

class CM_Usertext_Filter_Markdown_UnescapeBlockquoteTest extends CMTest_TestCase {

	public function tearDown() {
		CMTest_TH::clearEnv();
	}

	public function testProcess() {
		$text = <<<'EOD'
&gt; blockquote &gt; foo&gt;bar&gt;
    &gt; blockquote
EOD;
		$expected = <<<'EOD'
> blockquote &gt; foo&gt;bar&gt;
    > blockquote
EOD;
		$filter = new CM_Usertext_Filter_Markdown_UnescapeBlockquote();
		$actual = $filter->transform($text, new CM_Render());

		$this->assertSame($expected, $actual);
	}
}
