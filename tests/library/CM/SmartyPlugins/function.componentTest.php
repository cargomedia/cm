<?php

require_once CM_Util::getModulePath('CM') . 'library/CM/SmartyPlugins/function.component.php';

class smarty_function_componentTest extends CMTest_TestCase {

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

    public function testRender() {
        $html = new CM_Dom_NodeList(smarty_function_component(array('name' => 'CM_Component_Notfound'), $this->_template));
        $this->assertTrue($html->has('.CM_Component_Notfound'));
    }

    public function testRenderWithoutNamespace() {
        $html = new CM_Dom_NodeList(smarty_function_component(array('name' => '*_Component_Notfound'), $this->_template));
        $this->assertTrue($html->has('.CM_Component_Notfound'));
    }

    /**
     * @expectedException CM_Exception_Invalid
     * @expectedExceptionMessage The class was not found in any namespace.
     */
    public function testRenderNotExists() {
        smarty_function_component(array('name' => '*_Component_IsNotExisting'), $this->_template);
    }
}
