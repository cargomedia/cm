<?php

class CM_FormField_SiteTest extends CMTest_TestCase {

    public function testParseUserInput() {
        $this->getMockSite('CM_Site_Abstract', 123);
        $field = new CM_FormField_Site();
        $parsedInput = $field->parseUserInput(123);
        $this->assertInstanceOf('CM_Site_Abstract', $parsedInput);
    }

    /**
     * @expectedException CM_Exception_FormFieldValidation
     */
    public function testParseUserInputInvalid() {
        $field = new CM_FormField_Site();
        $field->parseUserInput(12345);
    }

    public function testValidate() {
        $environment = new CM_Frontend_Environment();
        $site = $this->getMockSite('CM_Site_Abstract', 1234);
        $field = new CM_FormField_Site();
        $field->validate($environment, $site);
    }

    /**
     * @expectedException CM_Exception_FormFieldValidation
     */
    public function testValidateInvalid() {
        $environment = new CM_Frontend_Environment();
        $field = new CM_FormField_Site();
        $field->validate($environment, 123);
    }
}
