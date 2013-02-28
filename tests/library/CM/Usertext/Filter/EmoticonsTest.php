<?php

class CM_Usertext_Filter_EmoticonsTest extends CMTest_TestCase {

	private static $_emoticonIdList = array();

	public static function setUpBeforeClass() {
		self::$_emoticonIdList[] = CM_Mysql::insert(TBL_CM_EMOTICON, array('code' => ':smiley:', 'codeAdditional' => ':),:-)', 'file' => '1.png'));
		self::$_emoticonIdList[] = CM_Mysql::insert(TBL_CM_EMOTICON, array('code' => ':<', 'file' => '2.png'));

		CMTest_TH::clearCache();
	}

	public static function tearDownAfterClass() {
		CMTest_TH::clearEnv();
	}

	public function testProcess() {
		$emoticonId = self::$_emoticonIdList[0];
		$text = 'foo :) bar :smiley:';
		$expected =
				'foo <span class="emoticon emoticon-' . $emoticonId . '" title="smiley"></span> bar <span class="emoticon emoticon-' .
						$emoticonId . '" title="smiley"></span>';
		$filter = new CM_Usertext_Filter_Emoticons();
		$actual = $filter->transform($text);

		$this->assertSame($expected, $actual);
	}
}
