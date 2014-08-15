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

    public function testRenderWithNamespace() {
        $html = new CM_Dom_NodeList(smarty_function_component(array('name' => 'Component_Notfound', 'namespace' => 'CM'), $this->_template));
        $this->assertTrue($html->has('.CM_Component_Notfound'));
    }

    public function testRenderWithoutNamespace() {
        $html = new CM_Dom_NodeList(smarty_function_component(array('name' => 'Component_Notfound'), $this->_template));
        $this->assertTrue($html->has('.CM_Component_Notfound'));
    }

    /**
     * @expectedException CM_Exception_Invalid
     * @expectedExceptionMessage The class was not found in any namespace.
     */
    public function testRenderNotExists() {
        smarty_function_component(array('name' => 'BAR_Component_Notfound'), $this->_template);
    }

    /**
     * @expectedException CM_Exception_Invalid
     * @expectedExceptionMessage Given namespace is not available.
     */
    public function testRenderNamespaceNotAvailable() {
        smarty_function_component(array('name' => 'Component_Notfound', 'namespace' => 'FOO'), $this->_template);
    }
}
