<?php

class CM_FormField_ColorPaletteTest extends CMTest_TestCase {

    public function testValidate() {
        $palette = [CM_Color_RGB::fromHexString('FF0000'), CM_Color_RGB::fromHexString('00FF00')];
        $field = new CM_FormField_ColorPalette(['name' => 'foo', 'palette' => $palette]);
        $environment = $this->getDefaultEnvironment();

        $value = $field->validate($environment, '00FF00');
        $this->assertSame('00FF00', $value->getHexString());
    }

    /**
     * @expectedException CM_Exception_FormFieldValidation
     */
    public function testValidateInvalid() {
        $palette = [CM_Color_RGB::fromHexString('FF0000'), CM_Color_RGB::fromHexString('00FF00')];
        $field = new CM_FormField_ColorPalette(['name' => 'foo', 'palette' => $palette]);
        $environment = $this->getDefaultEnvironment();

        $field->validate($environment, '#zzzzzzzzz');
    }

    /**
     * @expectedException CM_Exception_FormFieldValidation
     */
    public function testValidateNotInPalette() {
        $palette = [CM_Color_RGB::fromHexString('FF0000'), CM_Color_RGB::fromHexString('00FF00')];
        $field = new CM_FormField_ColorPalette(['name' => 'foo', 'palette' => $palette]);
        $environment = $this->getDefaultEnvironment();

        $field->validate($environment, '0000FF');
    }

    public function testRender() {
        $palette = [CM_Color_RGB::fromHexString('FF0000'), CM_Color_RGB::fromHexString('00FF00')];
        $field = new CM_FormField_ColorPalette(['name' => 'foo', 'palette' => $palette]);
        $field->setValue(CM_Color_RGB::fromHexString('00FF00'));

        $html = $this->_renderFormField($field);

        $this->assertSame('FF0000', $html->find('input')->getAttribute('value'));
        $this->assertSame(2, $html->find('input')->count());
    }

    public function testRenderWithValue() {
        $palette = [CM_Color_RGB::fromHexString('FF0000'), CM_Color_RGB::fromHexString('00FF00')];
        $field = new CM_FormField_ColorPalette(['name' => 'foo', 'palette' => $palette]);
        $field->setValue(CM_Color_RGB::fromHexString('00FF00'));

        $html = $this->_renderFormField($field);

        $this->assertSame('FF0000', $html->find('input')->getAttribute('value'));
        $this->assertSame(2, $html->find('input')->count());
    }

}
