<?php

require_once __DIR__ . '/../../../TestCase.php';

class CM_FormField_SetTest extends TestCase {

	public static function setupBeforeClass() {
	}

	public static function tearDownAfterClass() {
		TH::clearEnv();
	}

	public function testConstructor() {
		$field = new CM_FormField_Set('foo');
		$this->assertInstanceOf('CM_FormField_Set', $field);
	}

	public function testSetGetValue() {
		$field = new CM_FormField_Set('foo');

		$values = array(32 => 'apples');
		$field->setValue($values);
		$this->assertSame($values, $field->getValue());

		$value = 'bar';
		$field->setValue($value);
		$this->assertSame($value, $field->getValue());
	}

	public function testValidate() {
		$data = array(32 => 'apples', 64 => 'oranges', 128 => 'bananas');
		$field = new CM_FormField_Set('foo', $data, null, true);

		$userInputGood = array(32, 64, 128);
		$validationResult = $field->validate($userInputGood);
		$this->assertSame($userInputGood, $validationResult);

		$userInputTainted = array(32, 23, 132);
		$validationResult = $field->validate($userInputTainted);
		$this->assertSame(array(32), $validationResult);
	}

	public function testRender() {
		$name = 'foo';
		$data = array(32 => 'apples', 64 => 'oranges', 128 => 'bananas');
		$form = $this->getMockForm();
		$field = new CM_FormField_Set($name, $data, null, true);
		$values = array(64, 128);
		$field->setValue($values);
		$cssWidth = '50%';
		$field->setColumnSize($cssWidth);
		$doc = TH::renderFormField($form, $field, array());
		$this->assertTrue($doc->exists('ul[id="' . $form->getAutoId() . '-' . $name . '-input"]'));
		$this->assertSame(count($data), $doc->getCount('label'));
		$this->assertSame(count($data), $doc->getCount('input'));
		$this->assertSame($cssWidth, preg_replace('/^width: /', '', $doc->getAttr('li', 'style')));
		foreach ($data as $value => $label) {
			$this->assertTrue($doc->exists('li[class~="' . $name . '_value_' . $value . '"]'));
			$spanQuery = 'span[class="' . $name . '_label_' . $value . '"]';
			$this->assertTrue($doc->exists($spanQuery));
			$this->assertSame($label, $doc->getText($spanQuery));
			$this->assertTrue($doc->exists('input[value="' . $value . '"]'));
			if (in_array($value, $values)) {
				$this->assertSame('checked', $doc->getAttr('input[value="' . $value . '"]', 'checked'));
			}
		}
	}

}
