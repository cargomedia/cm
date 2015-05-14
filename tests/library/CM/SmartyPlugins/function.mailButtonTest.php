<?php

require_once CM_Util::getModulePath('CM') . 'library/CM/SmartyPlugins/function.mailButton.php';

class smarty_function_mailButtonTest extends CMTest_TestCase {

    /** @var Smarty_Internal_Template */
    private $_template;

    public function setUp() {
        $smarty = new Smarty();
        $render = new CM_Frontend_Render();
        $this->_template = $smarty->createTemplate('string:');
        $this->_template->assignGlobal('render', $render);
    }

    public function testRender() {
        $html = smarty_function_mailButton([
            'label' => 'Foo',
            'href'  => 'http://example.com',
        ], $this->_template);

        $this->assertContains('<a href="http://example.com"', $html);
        $this->assertContains('>Foo<', $html);
        $this->assertContains('border-style:solid;', $html);
    }
}
