<?php

class CM_FormField_UrlTest extends CMTest_TestCase {

    public function testValidate() {
        $field = new CM_FormField_Url(['name' => 'foo']);
        $environment = new CM_Frontend_Environment();
        $this->getMockForAbstractClass('CM_Response_Abstract', array(), '', false);

        $validUrls = array(
            'http://www.example.com/',
            'http://www.example.com/foo/bar',
            'http://www.exa-mple.com/',
            'http://www.example.com/?a=b&c=d',
            'http://www.example.com/#foo',
            'https://www.example.com/',
            'ftp://www.example.com/',
            'foo://www.example.com/',
        );

        foreach ($validUrls as $validUrl) {
            $parsedInput = $field->parseUserInput($validUrl);
            $field->validate($environment, $parsedInput);
            $this->assertSame($parsedInput, $parsedInput);
        }

        try {
            $parsedInput = $field->parseUserInput('http://www.öäü.com/');
            $field->validate($environment, $parsedInput);
            $this->fail('Non-ascii in host passed validation');
        } catch (CM_Exception_FormFieldValidation $e) {
            $this->assertTrue(true);
        }
        try {
            $parsedInput = $field->parseUserInput('http://www.example.com/öäü');
            $field->validate($environment, $parsedInput);
            $this->fail('Non-ascii in path passed validation');
        } catch (CM_Exception_FormFieldValidation $e) {
            $this->assertTrue(true);
        }
    }
}
