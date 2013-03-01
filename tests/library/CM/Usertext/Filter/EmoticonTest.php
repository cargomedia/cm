<?php

class CM_Usertext_Filter_EmoticonTest extends CMTest_TestCase {

	public function tearDown() {
		CMTest_TH::clearEnv();
	}

	public function testProcess() {
		$emoticonId = CM_Mysql::insert(TBL_CM_EMOTICON, array('code' => ':smiley:', 'codeAdditional' => ':),:-)', 'file' => '1.png'));
		$text = 'foo :) bar :smiley:';
		$expected =
				'foo <span class="emoticon emoticon-' . $emoticonId . '" title=":smiley:"></span> bar <span class="emoticon emoticon-' .
						$emoticonId . '" title=":smiley:"></span>';
		$filter = new CM_Usertext_Filter_Emoticon();
		$actual = $filter->transform($text);

		$this->assertSame($expected, $actual);
	}
}
