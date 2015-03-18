<?php

require_once CM_Util::getModulePath('CM') . 'library/CM/SmartyPlugins/function.lessVariable.php';

class smarty_function_lessVariableTest extends CMTest_TestCase {

    /** @var  Smarty_Internal_Template */
    private $_template;

    public function setUp() {
        $smarty = new Smarty();
        $render = new CM_Frontend_Render();
        $this->_template = $smarty->createTemplate('string:');
        $this->_template->assignGlobal('render', $render);
    }

    public function testColor() {
        $this->assertSame('#2d78e2', smarty_function_lessVariable(['name' => 'colorBrand'], $this->_template));
    }

    public function testSize() {
        $this->assertSame('14px', smarty_function_lessVariable(['name' => 'fontSize'], $this->_template));
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage is undefined
     */
    public function testInvalidName() {
        smarty_function_lessVariable(['name' => 'helloworld'], $this->_template);
    }
}
