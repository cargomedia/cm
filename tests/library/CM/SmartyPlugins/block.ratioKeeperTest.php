<?php

require_once CM_Util::getModulePath('CM') . 'library/CM/SmartyPlugins/block.ratioKeeper.php';

class smarty_block_ratioKeeperTest extends CMTest_TestCase {

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
        $params = ['ratio' => 1];
        $this->_assertContains('padding-bottom: 100%', $params);
        $params = ['ratio' => 0.75];
        $this->_assertContains('padding-bottom: 75%', $params);
        $params = ['ratio' => 0.33];
        $this->_assertContains('padding-bottom: 33%', $params);

        $params = ['height' => 600, 'width' => 900];
        $this->_assertContains('padding-bottom: 66%', $params);
        $params = ['height' => 900, 'width' => 600];
        $this->_assertContains('padding-bottom: 150%', $params);
    }

    public function testRenderContentAttrs() {
        $params = ['contentAttrs' => ['class' => 'test']];
        $this->_assertContains('class="test ratioKeeper-content"', $params);
    }

    /**
     * @param string $needle
     * @param array  $params
     */
    private function _assertContains($needle, array $params) {
        $this->assertContains($needle, smarty_block_ratioKeeper($params, '', $this->_template, false));
    }
}
