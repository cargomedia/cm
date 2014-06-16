<?php

class CM_FormField_DateTest extends CMTest_TestCase {

    /**
     * @expectedException CM_Exception_FormFieldValidation
     */
    public function testParseUserInputEmpty() {
        $formField = new CM_FormField_Date();
        $formField->parseUserInput(array());
    }

    /**
     * @expectedException CM_Exception_FormFieldValidation
     */
    public function testParseUserInputIncomplete() {
        $formField = new CM_FormField_Date();
        $formField->parseUserInput(array('year' => 1984, 'month' => 7));
    }

    public function testParseUserInputValid() {
        $formField = new CM_FormField_Date();
        $formField->parseUserInput(array('year' => 1984, 'month' => 7, 'day' => 2));

        $formField = new CM_FormField_Date();
        $formField->parseUserInput(array('year' => 2050, 'month' => 7, 'day' => 2));
        $this->assertTrue(true);
    }

    /**
     * @expectedException CM_Exception_FormFieldValidation
     */
    public function testValidateInvalidInput1() {
        $formField = new CM_FormField_Date();
        $environment = new CM_Frontend_Environment();
        $formField->validate($environment, 'foo');
    }

    /**
     * @expectedException CM_Exception_FormFieldValidation
     */
    public function testValidateInvalidInput2() {
        $formField = new CM_FormField_Date();
        $environment = new CM_Frontend_Environment();
        $formField->validate($environment, new DateTime('1789-05-01'));
    }

    /**
     * @expectedException CM_Exception_FormFieldValidation
     */
    public function testValidateInvalidInput3() {
        $formField = new CM_FormField_Date();
        $environment = new CM_Frontend_Environment();
        $formField->validate($environment, new DateTime('2110-05-01'));
    }

    public function testRender() {
        $field = new CM_FormField_Date(['name' => 'date']);
        $doc = $this->_renderFormField($field);

        $this->assertCount(3, $doc->find('select'));
        $this->assertCount(1, $doc->find(array('xpath' => '//select[@name="date[year]"]')));
        $this->assertCount(1, $doc->find(array('xpath' => '//select[@name="date[month]"]')));
        $this->assertCount(1, $doc->find(array('xpath' => '//select[@name="date[day]"]')));
    }
}
