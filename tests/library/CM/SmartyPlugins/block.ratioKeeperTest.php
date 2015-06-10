<?php

require_once CM_Util::getModulePath('CM') . 'library/CM/SmartyPlugins/block.contentPlaceholder.php';

class smarty_block_contentPlaceholderTest extends CMTest_TestCase {

    /**
     * @var Smarty_Internal_Template
     */
    private $_template;

    public function setUp() {
        $smarty = new Smarty();
        $render = new CM_Frontend_Render();
        $this->_template = $smarty->createTemplate('string:');
        $this->_template->assignGlobal('render', $render);
    }

    public function testRenderRatio() {
        $params = ['height' => 600, 'width' => 900];
        $size = $this->_getImgSize($params);
        $this->assertEquals(900, $size['width']);
        $this->assertEquals(600, $size['height']);
    }

    /**
     * @param array $params
     * @return String
     */
    private function _getImgSize(array $params) {
        $output = smarty_block_contentPlaceholder($params, '', $this->_template, false);
        $matches = array();
        preg_match('/class="contentPlaceholder-size" src="([^"]+)"/', $output, $matches);
        $imgSrc = $matches[1];
        $this->assertStringStartsWith('data:image/png;base64,', $imgSrc);
        $size = getimagesize($imgSrc);
        return ['width' => $size[0], 'height' => $size[1]];
    }
}
