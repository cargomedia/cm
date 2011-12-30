<?php
require_once dirname(__FILE__) . '/../../../TestCase.php';

class CM_FormField_TextTest extends TestCase {

	public static function setUpBeforeClass() {
	}

	public static function tearDownAfterClass() {
		TH::clearEnv();
	}

	public function testRender() {
		$field = new CM_FormField_Text('foo');
		$doc = TH::renderFormField($this->getMockForm(), $field, array());
		$this->assertSame(1, $doc->getCount('input'));
		$this->assertSame('<span id="formId-foo"><input name="foo" id="formId-foo-input" type="text" class="textinput " /><span class="messages"></span></span>', $doc->getHtml());
	}

	public function testRenderValue() {
		$field = new CM_FormField_Text('foo');
		$field->setValue('bar');
		$doc = TH::renderFormField($this->getMockForm(), $field, array());
		$this->assertSame('bar', $doc->getAttr('input', 'value'));
		$this->assertSame('<span id="formId-foo"><input name="foo" id="formId-foo-input" type="text" value="bar" class="textinput " /><span class="messages"></span></span>', $doc->getHtml());
	}
}
