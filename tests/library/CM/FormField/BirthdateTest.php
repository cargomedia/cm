<?php

class CM_FormField_BirthdateTest extends CMTest_TestCase {

    public function testValidate() {
        $formField = new CM_FormField_Birthdate(['name' => 'foo', 'minAge' => 18, 'maxAge' => 30]);
        $environment = new CM_Frontend_Environment();
        $parsedValue = $formField->parseUserInput(array('year' => 1984, 'month' => 1, 'day' => 1));
        $formField->validate($environment, $parsedValue);
        $this->assertEquals(new DateTime('1984-01-01'), $parsedValue);
    }

    /**
     * @expectedException CM_Exception_FormFieldValidation
     */
    public function testValidateInvalidYear()
    {
        $formField = new CM_FormField_Birthdate(['name' => 'foo', 'minAge' => 18, 'maxAge' => 30]);
        $environment = new CM_Frontend_Environment();
        $parsedValue = $formField->parseUserInput(array('year' => 2000, 'month' => 1, 'day' => 1));
        $formField->validate($environment, $parsedValue);
    }
}
