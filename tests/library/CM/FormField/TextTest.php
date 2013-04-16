<?php

class CM_FormField_TextTest extends CMTest_TestCase {

	public function testRender() {
		$form = $this->getMockForm();
		$field = new CM_FormField_Text('foo');
		$doc = $this->_renderFormField($form, $field);
		$this->assertSame(1, $doc->getCount('input'));
		$this->assertSame(
			'<div class="CM_FormField_Text CM_FormField_Abstract CM_View_Abstract" id="' . $form->getAutoId() . '-foo"><input name="foo" id="' .
					$form->getTagAutoId($field->getName() . '-input') .
					'" type="text" class="textinput " /><span class="messages"></span></div>', $doc->getHtml());
	}

	public function testRenderValue() {
		$form = $this->getMockForm();
		$field = new CM_FormField_Text('foo');
		$field->setValue('bar');
		$doc = $this->_renderFormField($form, $field);
		$this->assertSame('bar', $doc->getAttr('input', 'value'));
		$this->assertSame(
			'<div class="CM_FormField_Text CM_FormField_Abstract CM_View_Abstract" id="' . $form->getAutoId() . '-foo"><input name="foo" id="' .
					$form->getTagAutoId($field->getName() . '-input') .
					'" type="text" value="bar" class="textinput " /><span class="messages"></span></div>', $doc->getHtml());
	}
}
