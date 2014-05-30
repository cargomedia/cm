<?php

class CM_FormField_UrlTest extends CMTest_TestCase {

    public function testValidate() {
        $field = new CM_FormField_Url(['name' => 'foo']);
        $environment = new CM_Frontend_Environment();
        $response = $this->getMockForAbstractClass('CM_Response_Abstract', array(), '', false);

        $this->assertSame('http://www.example.com/', $field->validate($environment, 'http://www.example.com/'));
        $this->assertSame('http://www.example.com/foo/bar', $field->validate($environment, 'http://www.example.com/foo/bar'));
        $this->assertSame('http://www.exa-mple.com/', $field->validate($environment, 'http://www.exa-mple.com/'));
        $this->assertSame('http://www.example.com/?a=b&c=d', $field->validate($environment, 'http://www.example.com/?a=b&c=d'));
        $this->assertSame('http://www.example.com/#foo', $field->validate($environment, 'http://www.example.com/#foo'));
        $this->assertSame('https://www.example.com/', $field->validate($environment, 'https://www.example.com/'));
        $this->assertSame('ftp://www.example.com/', $field->validate($environment, 'ftp://www.example.com/'));
        $this->assertSame('foo://www.example.com/', $field->validate($environment, 'foo://www.example.com/'));
        try {
            $field->validate($environment, 'http://www.öäü.com/');
            $this->fail('Non-ascii in host passed validation');
        } catch (CM_Exception_FormFieldValidation $e) {
            $this->assertTrue(true);
        }
        try {
            $field->validate($environment, 'http://www.example.com/öäü');
            $this->fail('Non-ascii in path passed validation');
        } catch (CM_Exception_FormFieldValidation $e) {
            $this->assertTrue(true);
        }
    }
}
