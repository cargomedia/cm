<?php

class CM_FormField_GeoPointTest extends CMTest_TestCase {

	public function testValidate() {
		$field = new CM_FormField_GeoPoint('foo');
		$response = $this->getMockForAbstractClass('CM_Response_Abstract', array(), '', false);

		$this->assertSame(array('latitude' => -30.2, 'longitude' => -122.2),
			$field->validate(array('latitude' => -30.2, 'longitude' => -122.2), $response));

		$this->assertSame(array('latitude' => 30.2, 'longitude' => 122.2),
			$field->validate(array('latitude' => 30.2, 'longitude' => 122.2), $response));

		$this->assertSame(array('latitude' => 0.0, 'longitude' => 0.0),
			$field->validate(array('latitude' => 0, 'longitude' => 0), $response));

		try {
			$field->validate(array('latitude' => 300, 'longitude' => 20), $response);
			$this->fail('Out of range latitude passed validation');
		} catch (CM_Exception_FormFieldValidation $e) {
			$this->assertTrue(true);
		}

		try {
			$field->validate(array('latitude' => -30.2, 'longitude' => 300), $response);
			$this->fail('Out of range longitude passed validation');
		} catch (CM_Exception_FormFieldValidation $e) {
			$this->assertTrue(true);
		}

		try {
			$field->validate(array('latitude' => 30), $response);
			$this->fail('Missing longitude passed validation');
		} catch (CM_Exception_FormFieldValidation $e) {
			$this->assertTrue(true);
		}

		try {
			$field->validate(array('latitude' => 30), $response);
			$this->fail('Missing longitude passed validation');
		} catch (CM_Exception_FormFieldValidation $e) {
			$this->assertTrue(true);
		}

		try {
			$field->validate(array('latitude' => 'foo', 'longitude' => 30), $response);
			$this->fail('Non-numeric latitude passed validation');
		} catch (CM_Exception_FormFieldValidation $e) {
			$this->assertTrue(true);
		}

		try {
			$field->validate(array('latitude' => 30, 'longitude' => 'foo'), $response);
			$this->fail('Non-numeric longitude passed validation');
		} catch (CM_Exception_FormFieldValidation $e) {
			$this->assertTrue(true);
		}
	}
}
