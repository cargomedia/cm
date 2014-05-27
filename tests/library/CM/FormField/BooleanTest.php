<?php

class CM_FormField_BooleanTest extends CMTest_TestCase {

    public function testConstructor() {
        $field = new CM_FormField_BooleanTest();
        $this->assertInstanceOf('CM_FormField_BooleanTest', $field);
    }

    public function testRender() {
        $form = $this->getMockForm();
        $field = new CM_FormField_Boolean();
        $doc = $this->_renderFormField($form, $field, 'foo');

        $this->assertFalse($doc->exists('.switch'));
        $this->assertTrue($doc->exists('input[name="foo"][value="0"]'));
        $this->assertTrue($doc->exists('input[name="foo"][value="1"]'));
    }

    public function testRenderSwitch() {
        $form = $this->getMockForm();
        $field = new CM_FormField_Boolean();
        $doc = $this->_renderFormField($form, $field, 'foo', array('template' => 'switch'));

        $this->assertTrue($doc->exists('.switch'));
    }
}
