<?php
require_once __DIR__ . '/../../../TestCase.php';

class CM_FormField_TextTest extends TestCase {

	public static function setUpBeforeClass() {
	}

	public static function tearDownAfterClass() {
		TH::clearEnv();
	}

	public function testRender() {
		$form = $this->getMockForm();
		$field = new CM_FormField_Text('foo');
		$doc = TH::renderFormField($form, $field, array());
		$this->assertSame(1, $doc->getCount('input'));
		$this->assertSame('<span id="' . $form->getAutoId() . '-foo"><input name="foo" id="' . $form->getAutoId() .
				'-foo-input" type="text" class="textinput " /><span class="messages"></span></span>', $doc->getHtml());
	}

	public function testRenderValue() {
		$form = $this->getMockForm();
		$field = new CM_FormField_Text('foo');
		$field->setValue('bar');
		$doc = TH::renderFormField($form, $field, array());
		$this->assertSame('bar', $doc->getAttr('input', 'value'));
		$this->assertSame('<span id="' . $form->getAutoId() . '-foo"><input name="foo" id="' . $form->getAutoId() .
				'-foo-input" type="text" value="bar" class="textinput " /><span class="messages"></span></span>', $doc->getHtml());
	}
}
