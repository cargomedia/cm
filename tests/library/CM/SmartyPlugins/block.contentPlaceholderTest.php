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

    public function testRenderColor() {
        $params = ['height' => 1, 'width' => 1, 'color' => [127, 137, 147, 50]];
        $colors = $this->_getImgColors($params);
        $this->assertEquals(127, $colors['red']);
        $this->assertEquals(137, $colors['green']);
        $this->assertEquals(147, $colors['blue']);
        $this->assertEquals(50, $colors['alpha']);
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

    /**
     * @param array $params
     * @return array
     */
    private function _getImgColors(array $params) {
        $output = smarty_block_contentPlaceholder($params, '', $this->_template, false);
        $matches = array();
        preg_match('/class="contentPlaceholder-size" src="([^"]+)"/', $output, $matches);
        $imgSrc = $matches[1];
        $this->assertStringStartsWith('data:image/png;base64,', $imgSrc);
        $img = imagecreatefrompng($imgSrc);
        $rgb = imagecolorat($img, 0, 0);
        return imagecolorsforindex($img, $rgb);
    }
}
