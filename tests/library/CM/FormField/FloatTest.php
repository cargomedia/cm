<?php

class CM_FormField_FloatTest extends CMTest_TestCase {

	public function testValidate() {
		$field = new CM_FormField_Float('foo');
		$response = $this->getMockForAbstractClass('CM_Response_Abstract', array(), '', false);

		$validationResult = $field->validate(1.3, $response);
		$this->assertSame(1.3, $validationResult);
		try {
			$field->validate('foo', $response);
			$this->fail('Could insert text in float formfield');
		} catch (CM_Exception_FormFieldValidation $e) {
			$this->assertTrue(true);
		}
	}
}
