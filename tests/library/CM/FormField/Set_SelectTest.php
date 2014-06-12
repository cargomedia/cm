<?php

class CM_FormField_Set_SelectTest extends CMTest_TestCase {

    public function testValidateGood() {
        $data = array(32 => 'apples', 64 => 'oranges', 128 => 'bananas');
        $field = new CM_FormField_Set_Select(['name' => 'foo', 'values' => $data, 'labelsInValues' => true]);

        $environment = new CM_Frontend_Environment();
        $userInputGood = 64;
        $parsedInput = $field->parseUserInput($userInputGood);
        $field->validate($environment, $parsedInput);
        $this->assertSame($userInputGood, $parsedInput);
    }

    /**
     * @expectedException CM_Exception_FormFieldValidation
     */
    public function testValidateBad1() {
        $data = array(32 => 'apples', 64 => 'oranges', 128 => 'bananas');
        $field = new CM_FormField_Set_Select(['name' => 'foo', 'values' => $data, 'labelsInValues' => true]);

        $environment = new CM_Frontend_Environment();
        $userInputBad = 11;
        $parsedInput = $field->parseUserInput($userInputBad);
        $field->validate($environment, $parsedInput);
    }

    /**
     * @expectedException CM_Exception_FormFieldValidation
     */
    public function testValidateBad2() {
        $data = array(32 => 'apples', 64 => 'oranges', 128 => 'bananas');
        $field = new CM_FormField_Set_Select(['name' => 'foo', 'values' => $data, 'labelsInValues' => true]);

        $environment = new CM_Frontend_Environment();
        $userInputBad = array(32);
        $parsedInput = $field->parseUserInput($userInputBad);
        $field->validate($environment, $parsedInput);
    }
}
