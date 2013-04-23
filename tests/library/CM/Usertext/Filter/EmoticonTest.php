<?php

class CM_Usertext_Filter_EmoticonTest extends CMTest_TestCase {

	public function tearDown() {
		CMTest_TH::clearEnv();
	}

	public function testProcess() {
		$site = $this->getMockSite(24, null, 'http://www.default.dev');

		$emoticonId = CM_Db_Db::insert(TBL_CM_EMOTICON, array('code' => ':smiley:', 'codeAdditional' => ':),:-)', 'file' => '1.png'));
		$text = 'foo :) bar :smiley:';
		$expected =
				'foo <img src="http://www.default.dev/layout/24/0/img/emoticon/1.png" class="emoticon emoticon-' . $emoticonId .
						'" title=":smiley:" /> bar <img src="http://www.default.dev/layout/24/0/img/emoticon/1.png" class="emoticon emoticon-' .
						$emoticonId . '" title=":smiley:" />';
		$filter = new CM_Usertext_Filter_Emoticon();
		$actual = $filter->transform($text, new CM_Render($site));

		$this->assertSame($expected, $actual);
	}

	public function testFixedHeight() {
		$site = $this->getMockSite(24, null, 'http://www.default.dev');

		$emoticonId = CM_Db_Db::insert(TBL_CM_EMOTICON, array('code' => ':smiley:', 'codeAdditional' => ':),:-)', 'file' => '1.png'));
		$text = 'foo :) bar :smiley:';
		$expected =
				'foo <img src="http://www.default.dev/layout/24/0/img/emoticon/1.png" class="emoticon emoticon-' . $emoticonId .
						'" title=":smiley:" height="16" /> bar <img src="http://www.default.dev/layout/24/0/img/emoticon/1.png" class="emoticon emoticon-' .
						$emoticonId . '" title=":smiley:" height="16" />';
		$filter = new CM_Usertext_Filter_Emoticon(16);
		$actual = $filter->transform($text, new CM_Render($site));

		$this->assertSame($expected, $actual);
	}
}
