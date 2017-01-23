<?php

class CM_FormField_ColorTest extends CMTest_TestCase {

    public function testValidate() {
        $field = new CM_FormField_Color(['name' => 'foo']);
        $environment = $this->getDefaultEnvironment();

        $value = $field->validate($environment, '#00FF00');
        $this->assertSame('00FF00', $value->getHexString());
    }

    /**
     * @expectedException CM_Exception_FormFieldValidation
     */
    public function testValidateInvalid() {
        $field = new CM_FormField_Color(['name' => 'foo']);
        $environment = $this->getDefaultEnvironment();

        $field->validate($environment, '#zzzzzzzzz');
    }

    public function testRender() {
        $field = new CM_FormField_Color(['name' => 'foo']);
        $field->setValue(CM_Color_RGB::fromHexString('00FF00'));

        $html = $this->_renderFormField($field);

        $this->assertSame('color', $html->find('input')->getAttribute('type'));
        $this->assertSame('#00FF00', $html->find('input')->getAttribute('value'));
    }

}
