<?php

require_once CM_Util::getModulePath('CM') . 'library/CM/SmartyPlugins/function.checkbox.php';

class smarty_function_checkboxTest extends CMTest_TestCase {

    /** @var Smarty_Internal_Template */
    private $_template;

    public function setUp() {
        $smarty = new Smarty();
        $render = new CM_Frontend_Render();
        $this->_template = $smarty->createTemplate('string:');
        $this->_template->assignGlobal('render', $render);
    }

    public function testDefault() {
        $this->_assertSame('<input type="checkbox" id="foo"><label for="foo"><span class="label">Hello</span></label>', array(
            'id'    => 'foo',
            'label' => 'Hello',
        ));
    }

    /**
     * @expectedException ErrorException
     */
    public function testNoLabel() {
        smarty_function_checkbox(['id' => 'foo'], $this->_template);
    }

    public function testNoId() {
        $html = smarty_function_checkbox(['label' => 'Hello'], $this->_template);
        $this->assertRegExp('#<input type="checkbox" id=".+">#', $html);
    }

    public function testChecked() {
        $this->_assertSame('<input type="checkbox" id="foo" checked="checked"><label for="foo"><span class="label">Hello</span></label>', array(
            'id'      => 'foo',
            'label'   => 'Hello',
            'checked' => true,
        ));
    }

    public function testAdditionalAttributes() {
        $this->_assertSame('<input type="checkbox" id="foo" class="my class" name="my name" tabindex="12" value="my value" data-click-disable="true"><label for="foo"><span class="label">Hello</span></label>', array(
            'id'       => 'foo',
            'label'    => 'Hello',
            'class'    => 'my class',
            'name'     => 'my name',
            'tabindex' => '12',
            'value'    => 'my value',
            'data'     => [
                'click-disable' => "true",
            ],
        ));
    }

    public function testSwitch() {
        $this->_assertSame('<input type="checkbox" id="foo" class="checkbox-switch"><label for="foo"><span class="handle"></span><span class="label">Hello</span></label>', array(
            'id'      => 'foo',
            'label'   => 'Hello',
            'display' => CM_FormField_Boolean::DISPLAY_SWITCH,
        ));
    }

    /**
     * @param string $expected
     * @param array  $params
     */
    private function _assertSame($expected, array $params) {
        $this->assertSame($expected, smarty_function_checkbox($params, $this->_template));
    }
}
