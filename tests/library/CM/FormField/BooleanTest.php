<?php

class CM_FormField_BooleanTest extends CMTest_TestCase {

    public function testRenderCheckbox() {
        $field = new CM_FormField_Boolean(['name' => 'checkbox']);
        $doc = $this->_renderFormField($field);

        $this->assertCount(0, $doc->find('.switch'));
        $this->assertCount(1, $doc->find('.checkbox'));
        $this->assertCount(2, $doc->find('input[name="checkbox"]'));
        $this->assertSame('0', $doc->find('input[name="checkbox"][type="hidden"]')->getAttribute('value'));
        $this->assertSame('1', $doc->find('input[name="checkbox"][type="checkbox"]')->getAttribute('value'));
    }

    public function testRenderSwitch() {
        $field = new CM_FormField_Boolean(['name' => 'switch']);
        $doc = $this->_renderFormField($field, array('display' => CM_FormField_Boolean::DISPLAY_SWITCH));

        $this->assertCount(1, $doc->find('.switch'));
        $this->assertCount(1, $doc->find('.handle'));
        $this->assertCount(0, $doc->find('.checkbox'));
        $this->assertCount(2, $doc->find('input[name="switch"]'));
        $this->assertSame('0', $doc->find('input[name="switch"][type="hidden"]')->getAttribute('value'));
        $this->assertSame('1', $doc->find('input[name="switch"][type="checkbox"]')->getAttribute('value'));
    }
}
