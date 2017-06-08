<?php

use CM\Url\AppUrl;

class CM_Usertext_Filter_EmoticonTest extends CMTest_TestCase {

    /** @var CM_Site_Abstract */
    protected $_mockSite;

    public function setUp() {
        $this->_mockSite = $this->getMockSite(null, 24, array(
            'url'    => 'http://www.default.dev',
            'urlCdn' => 'http://cdn.default.dev',
        ));
    }

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testProcess() {
        $text = 'foo :) bar :smiley:';
        $expected = 'foo ' . $this->_getEmoticonImg(':)') . ' bar ' . $this->_getEmoticonImg(':smiley:');
        $filter = new CM_Usertext_Filter_Emoticon();
        $actual = $filter->transform($text, new CM_Frontend_Render(new CM_Frontend_Environment($this->_mockSite)));

        $this->assertSame($expected, $actual);
    }

    public function testFixedHeight() {
        $text = 'foo :) bar :smiley:';
        $expected = 'foo ' . $this->_getEmoticonImg(':)', 16) . ' bar ' . $this->_getEmoticonImg(':smiley:', 16);
        $filter = new CM_Usertext_Filter_Emoticon(16);
        $actual = $filter->transform($text, new CM_Frontend_Render(new CM_Frontend_Environment($this->_mockSite)));

        $this->assertSame($expected, $actual);
    }

    public function testFalseSmileys() {
        $text = '(2003) (php3) (2008) (win8) (100%) (50 %) (B) (B2B) (O) (CEO) İÖO) ১ %) ' .
            '3) 8) %) B) O) foo!8)bar';
        $expected = '(2003) (php3) (2008) (win8) (100%) (50 %) (B) (B2B) (O) (CEO) İÖO) ১ %) ' .
            $this->_getEmoticonImg('3)') . ' ' .
            $this->_getEmoticonImg('8)') . ' ' .
            $this->_getEmoticonImg('%)') . ' ' .
            $this->_getEmoticonImg('B)') . ' ' .
            $this->_getEmoticonImg('O)') . ' foo!' .
            $this->_getEmoticonImg('8)') . 'bar';
        $filter = new CM_Usertext_Filter_Emoticon();
        $actual = $filter->transform($text, new CM_Frontend_Render(new CM_Frontend_Environment($this->_mockSite)));

        $this->assertSame($expected, $actual);
    }

    protected function _getEmoticonImg($emoticonCode, $height = null) {
        $emoticon = CM_Emoticon::findByCode($emoticonCode);
        if (!$emoticon) {
            throw new CM_Exception_Invalid('Cannot find emoticon for code `' . $emoticonCode . '`.');
        }
        $deployVersion = CM_App::getInstance()->getDeployVersion();
        $url = new AppUrl('layout/img/emoticon/' . $emoticon->getFileName());
        $url->setDeployVersion($deployVersion);
        $url = $url
            ->withSite($this->_mockSite)
            ->withBaseUrl($this->_mockSite->getUrlCdn());
        $heightAttribute = $height ? ' height="' . $height . '"' : '';
        return '<img src="' . $url . '" class="emoticon emoticon-' . $emoticon->getName() . '" title="' . $emoticon->getDefaultCode() . '"' .
            $heightAttribute . ' />';
    }
}
