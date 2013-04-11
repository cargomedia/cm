<?php

class CM_Usertext_Filter_EmoticonTest extends CMTest_TestCase {

	public function tearDown() {
		CMTest_TH::clearEnv();
	}

	public function testProcess() {
		$siteType = CM_Site_Abstract::factory()->getType();
		$emoticonId = CM_Db_Db::insert(TBL_CM_EMOTICON, array('code' => ':smiley:', 'codeAdditional' => ':),:-)', 'file' => '1.png'));
		$text = 'foo :) bar :smiley:';
		$expected =
				'foo <img src="http://www.default.dev/layout/' . $siteType . '/0/img/emoticon/1.png" class="emoticon emoticon-' . $emoticonId .
						'" title=":smiley:" /> bar <img src="http://www.default.dev/layout/' . $siteType . '/0/img/emoticon/1.png" class="emoticon emoticon-' .
						$emoticonId .
						'" title=":smiley:" />';
		$filter = new CM_Usertext_Filter_Emoticon();
		$actual = $filter->transform($text, new CM_Render());

		$this->assertSame($expected, $actual);
	}
}
