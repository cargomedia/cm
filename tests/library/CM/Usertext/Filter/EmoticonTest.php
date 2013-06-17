<?php

class CM_Usertext_Filter_EmoticonTest extends CMTest_TestCase {

	public function setUp() {
		$this->_mockSite = $this->getMockSite(24, null, 'http://www.default.dev');
		foreach ($this->_emoticonData as $emoticonCode => $emoticonData) {
			$emoticonData['code'] = $emoticonCode;
			$this->_emoticonId[$emoticonCode] = CM_Db_Db::insert(TBL_CM_EMOTICON, $emoticonData);
		}
	}

	public function tearDown() {
		CMTest_TH::clearEnv();
	}

	public function testProcess() {
		$text = 'foo :) bar :smiley:';
		$expected = 'foo ' . $this->_getEmoticonImg(':smiley:') . ' bar ' . $this->_getEmoticonImg(':smiley:');
		$filter = new CM_Usertext_Filter_Emoticon();
		$actual = $filter->transform($text, new CM_Render($this->_mockSite));

		$this->assertSame($expected, $actual);
	}

	public function testFixedHeight() {
		$text = 'foo :) bar :smiley:';
		$expected = 'foo ' . $this->_getEmoticonImg(':smiley:', 16) . ' bar ' . $this->_getEmoticonImg(':smiley:', 16);
		$filter = new CM_Usertext_Filter_Emoticon(16);
		$actual = $filter->transform($text, new CM_Render($this->_mockSite));

		$this->assertSame($expected, $actual);
	}

	public function testFalseSmileys() {
		$text = '(2003) (php3) (2008) (win8) (100%) (50 %) (B) (B2B) (O) (CEO) IÖO) ১ %) ' .
				'3) 8) %) B) O) foo!8)bar';
		$expected = '(2003) (php3) (2008) (win8) (100%) (50 %) (B) (B2B) (O) (CEO) IÖO) ১ %) ' .
				$this->_getEmoticonImg(':imp:') . ' ' .
				$this->_getEmoticonImg(':sunglasses:') . ' ' .
				$this->_getEmoticonImg(':dizzy_face:') . ' ' .
				$this->_getEmoticonImg(':sunglasses:') . ' ' .
				$this->_getEmoticonImg(':innocent:') . ' foo!' .
				$this->_getEmoticonImg(':sunglasses:') . 'bar';
		$filter = new CM_Usertext_Filter_Emoticon();
		$actual = $filter->transform($text, new CM_Render($this->_mockSite));

		$this->assertSame($expected, $actual);
	}

	protected $_emoticonData = array(
		':smiley:'     => array('codeAdditional' => ':),:-)', 'file' => '1.png'),
		':imp:'        => array('codeAdditional' => '3),3-)', 'file' => 'imp.png'),
		':sunglasses:' => array('codeAdditional' => 'B-),B),8-),8)', 'file' => 'sunglasses.png'),
		':dizzy_face:' => array('codeAdditional' => '%-),%),O.o,o.O', 'file' => 'dizzy_face.png'),
		':innocent:'   => array('codeAdditional' => 'O),o-)', 'file' => 'innocent.png'),
	);

	/** @var  array */
	protected $_emoticonId;

	/** @var CM_Site_Abstract */
	protected $_mockSite;

	protected function _getEmoticonImg($emoticonCode, $height = null) {
		$siteUrl = $this->_mockSite->getUrl();
		$siteType = $this->_mockSite->getId();
		$deployVersion = CM_App::getInstance()->getDeployVersion();
		$emoticonFile = $this->_emoticonData[$emoticonCode]['file'];
		$emoticonId = $this->_emoticonId[$emoticonCode];
		$heightAttribute = $height ? ' height="' . $height . '"' : '';
		return '<img src="' . $siteUrl . '/layout/' . $siteType . '/' . $deployVersion . '/img/emoticon/' . $emoticonFile .
		'" class="emoticon emoticon-' . $emoticonId . '" title="' . $emoticonCode . '"' . $heightAttribute . ' />';
	}
}
