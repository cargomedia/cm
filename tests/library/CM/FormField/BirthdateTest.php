<?php

class CM_FormField_BirthdateTest extends CMTest_TestCase {

	/**
	 * @expectedException CM_Exception_FormFieldValidation
	 */
	public function testValidate() {
		$formfield = new CM_FormField_Birthdate(18, 30);
		$request = CM_Request_Abstract::factory('get', '/foo');
		$response = CM_Response_Abstract::factory($request);
		$value = $formfield->validate(array('year' => 1995, 'month' => 1, 'day' => 1), $response);
		$this->assertEquals(new DateTime('1995-01-01'), $value);

		$formfield->validate(array('year' => 2005, 'month' => 1, 'day' => 1), $response);
	}
}
