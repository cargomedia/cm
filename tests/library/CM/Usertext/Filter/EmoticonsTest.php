<?php

class CM_Usertext_Filter_EmoticonsTest extends CMTest_TestCase {

	public static function tearDownAfterClass() {
		CMTest_TH::clearEnv();
	}

	public function testProcess() {

		CM_Mysql::insert(TBL_CM_EMOTICON, array('code' => ':),:-),:smiley:', 'file' => '1.png'));

		$text = 'foo :) bar :smiley:';
		$expected = 'foo <img class="emoticon" title=":)" alt=":)" src="/img/emoticons/1.png" /> bar <img class="emoticon" title=":smiley:" alt=":smiley:" src="/img/emoticons/1.png" />';
		$filter = new CM_Usertext_Filter_Emoticons();
		$actual = $filter->transform($text);

		$this->assertSame($expected, $actual);
	}

}
