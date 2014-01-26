<?php

class CM_FormField_UrlTest extends CMTest_TestCase {

	public function testValidate() {
		$field = new CM_FormField_Url('foo');
		$response = $this->getMockForAbstractClass('CM_Response_Abstract', array(), '', false);

		$this->assertSame('http://www.example.com/', $field->validate('http://www.example.com/', $response));
		$this->assertSame('http://www.example.com/foo/bar', $field->validate('http://www.example.com/foo/bar', $response));
		$this->assertSame('http://www.exa-mple.com/', $field->validate('http://www.exa-mple.com/', $response));
		$this->assertSame('http://www.example.com/?a=b&c=d', $field->validate('http://www.example.com/?a=b&c=d', $response));
		$this->assertSame('http://www.example.com/#foo', $field->validate('http://www.example.com/#foo', $response));
		$this->assertSame('https://www.example.com/', $field->validate('https://www.example.com/', $response));
		$this->assertSame('ftp://www.example.com/', $field->validate('ftp://www.example.com/', $response));
		$this->assertSame('foo://www.example.com/', $field->validate('foo://www.example.com/', $response));
		try {
			$field->validate('http://www.öäü.com/', $response);
			$this->fail('Non-ascii in host passed validation');
		} catch (CM_Exception_FormFieldValidation $e) {
			$this->assertTrue(true);
		}
		try {
			$field->validate('http://www.example.com/öäü', $response);
			$this->fail('Non-ascii in path passed validation');
		} catch (CM_Exception_FormFieldValidation $e) {
			$this->assertTrue(true);
		}
	}
}
