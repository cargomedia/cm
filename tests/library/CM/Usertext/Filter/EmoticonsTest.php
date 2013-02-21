<?php

class CM_Usertext_Filter_EmoticonsTest extends CMTest_TestCase {

	public static function tearDownAfterClass() {
		CMTest_TH::clearEnv();
	}

	public function testProcess() {

		$emoticonId = CM_Mysql::insert(TBL_CM_EMOTICON, array('code' => ':),:-),:smiley:', 'file' => '1.png'));

		$text = 'foo :) bar :smiley:';
		$expected = 'foo <span class="emoticon emoticon-'.$emoticonId.'" title=":)"></span> bar <span class="emoticon emoticon-'.$emoticonId.'" title=":smiley:"></span>';
		$filter = new CM_Usertext_Filter_Emoticons();
		$actual = $filter->transform($text);

		$this->assertSame($expected, $actual);
	}

}
