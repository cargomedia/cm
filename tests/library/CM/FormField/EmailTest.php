<?php

class CM_FormField_EmailTest extends CMTest_TestCase {

    public function testValidateValid() {
        $environment = new CM_Frontend_Environment();

        $expected = 'foo@example.com';
        $formField = new CM_FormField_Email();
        $parsedUserInput = $formField->parseUserInput($expected);
        $formField->validate($environment, $parsedUserInput);
        $this->assertSame($expected, $parsedUserInput);

        $expected = 'foo+123@example.com';
        $formField = new CM_FormField_Email();
        $parsedUserInput = $formField->parseUserInput($expected);
        $formField->validate($environment, $parsedUserInput);
        $this->assertSame($expected, $parsedUserInput);
    }

    /**
     * @expectedException CM_Exception_FormFieldValidation
     */
    public function testValidateInvalid() {
        $environment = new CM_Frontend_Environment();

        $expected = 'fooBar';
        $formField = new CM_FormField_Email();
        $parsedUserInput = $formField->parseUserInput($expected);
        $formField->validate($environment, $parsedUserInput);
        $this->assertSame($expected, $parsedUserInput);
    }
}
