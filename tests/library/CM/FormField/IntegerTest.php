<?php

class CM_FormField_IntegerTest extends CMTest_TestCase {

    public function testParseUserInput() {
        $field = new CM_FormField_Integer();

        $parsedInput = $field->parseUserInput(2);
        $this->assertSame(2, $parsedInput);

        $parsedInput = $field->parseUserInput(2.3);
        $this->assertSame(2, $parsedInput);

        $parsedInput = $field->parseUserInput('2');
        $this->assertSame(2, $parsedInput);

        $parsedInput = $field->parseUserInput('2foo');
        $this->assertSame(2, $parsedInput);

        $parsedInput = $field->parseUserInput('foo2');
        $this->assertSame(0, $parsedInput);
    }

    public function testValidate() {
        $environment = new CM_Frontend_Environment();
        $field = new CM_FormField_Integer(['min' => -10, 'max' => 20]);

        $field->validate($environment, -10);
        $field->validate($environment, 20);
    }

    /**
     * @expectedException CM_Exception_FormFieldValidation
     */
    public function testValidateTooBig() {
        $environment = new CM_Frontend_Environment();
        $field = new CM_FormField_Integer(['min' => -10, 'max' => 20]);
        $field->validate($environment, 21);
    }

    /**
     * @expectedException CM_Exception_FormFieldValidation
     */
    public function testValidateTooSmall() {
        $environment = new CM_Frontend_Environment();
        $field = new CM_FormField_Integer(['min' => -10, 'max' => 20]);
        $field->validate($environment, -11);
    }
}
