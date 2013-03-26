<?php

class CM_Usertext_Filter_EmoticonTest extends CMTest_TestCase {

	public function tearDown() {
		CMTest_TH::clearEnv();
	}

	public function testProcess() {
		$emoticonId = CM_Db_Db::insert(TBL_CM_EMOTICON, array('code' => ':smiley:', 'codeAdditional' => ':),:-)', 'file' => '1.png'));
		$text = 'foo :) bar :smiley:';
		$expected =
				'foo <img src="/layout/1/0/img/emoticon/1.png" class="emoticon emoticon-' . $emoticonId .
						'" title=":smiley:" /> bar <img src="/layout/1/0/img/emoticon/1.png" class="emoticon emoticon-' . $emoticonId .
						'" title=":smiley:" />';
		$filter = new CM_Usertext_Filter_Emoticon();
		$actual = $filter->transform($text, $this->_getRender());

		$this->assertSame($expected, $actual);
	}
}
