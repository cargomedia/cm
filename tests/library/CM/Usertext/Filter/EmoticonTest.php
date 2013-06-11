<?php

class CM_Usertext_Filter_EmoticonTest extends CMTest_TestCase {

	public function tearDown() {
		CMTest_TH::clearEnv();
	}

	public function testProcess() {
		$site = $this->getMockSite(24, null, 'http://www.default.dev');

		$emoticonId = CM_Db_Db::insert(TBL_CM_EMOTICON, array('code' => ':smiley:', 'codeAdditional' => ':),:-)', 'file' => '1.png'));
		$text = 'foo :) bar :smiley:';
		$expected = 'foo ' . $this->_getEmoticonImg(':smiley:', '1.png', $emoticonId) .
				' bar ' . $this->_getEmoticonImg(':smiley:', '1.png', $emoticonId);
		$filter = new CM_Usertext_Filter_Emoticon();
		$actual = $filter->transform($text, new CM_Render($site));

		$this->assertSame($expected, $actual);
	}

	public function testFixedHeight() {
		$site = $this->getMockSite(24, null, 'http://www.default.dev');

		$emoticonId = CM_Db_Db::insert(TBL_CM_EMOTICON, array('code' => ':smiley:', 'codeAdditional' => ':),:-)', 'file' => '1.png'));
		$text = 'foo :) bar :smiley:';
		$expected = 'foo ' . $this->_getEmoticonImg(':smiley:', '1.png', $emoticonId, 16) .
				' bar ' . $this->_getEmoticonImg(':smiley:', '1.png', $emoticonId, 16);
		$filter = new CM_Usertext_Filter_Emoticon(16);
		$actual = $filter->transform($text, new CM_Render($site));

		$this->assertSame($expected, $actual);
	}

	public function testFalseSmileys() {
		$site = $this->getMockSite(24, null, 'http://www.default.dev');

		$emoticonId = array();
		foreach (array(
					 'imp'        => array('code' => ':imp:', 'codeAdditional' => '3),3-)', 'file' => 'imp.png'),
					 'sunglasses' => array('code' => ':sunglasses:', 'codeAdditional' => 'B-),B),8-),8)', 'file' => 'sunglasses.png'),
					 'dizzy_face' => array('code' => ':dizzy_face:', 'codeAdditional' => '%-),%),O.o,o.O', 'file' => 'dizzy_face.png'),
					 'innocent'   => array('code' => ':innocent:', 'codeAdditional' => 'O),o-)', 'file' => 'innocent.png'),
				 ) as $emoticonName => $emoticonData) {
			$emoticonId[$emoticonName] = CM_Db_Db::insert(TBL_CM_EMOTICON, $emoticonData);
		}

		$text = '(2003) (2008) (100%) (B) (O) 3) 8) %) B) O)';
		$expected = '(2003) (2008) (100%) (B) (O) ' . $this->_getEmoticonImg(':imp:', 'imp.png', $emoticonId['imp']) .
				' ' . $this->_getEmoticonImg(':sunglasses:', 'sunglasses.png', $emoticonId['sunglasses']) .
				' ' . $this->_getEmoticonImg(':dizzy_face:', 'dizzy_face.png', $emoticonId['dizzy_face']) .
				' ' . $this->_getEmoticonImg(':sunglasses:', 'sunglasses.png', $emoticonId['sunglasses']) .
				' ' . $this->_getEmoticonImg(':innocent:', 'innocent.png', $emoticonId['innocent']);
		$filter = new CM_Usertext_Filter_Emoticon();
		$actual = $filter->transform($text, new CM_Render($site));

		$this->assertSame($expected, $actual);
	}

	protected function _getEmoticonImg($emoticonCode, $emoticonFile, $emoticonId, $height = null) {
		$siteType = 24;
		$siteUrl = 'http://www.default.dev';
		$heightAttribute = $height ? ' height="' . $height . '"' : '';
		return '<img src="' . $siteUrl . '/layout/' . $siteType . '/' . CM_App::getInstance()->getDeployVersion() . '/img/emoticon/' . $emoticonFile .
		'" class="emoticon emoticon-' . $emoticonId . '" title="' . $emoticonCode . '"' . $heightAttribute . ' />';
	}
}
