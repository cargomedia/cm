<?php

class CM_FormField_ColorTest extends CMTest_TestCase {

    public function testValidate() {
        $formField = new CM_FormField_Color();
        $environment = new CM_Frontend_Environment();
        $formField->validate($environment, '#1234EF');
        $formField->validate($environment, '#1234ef');
    }

    /**
     * @expectedException CM_Exception_FormFieldValidation
     */
    public function testValidateInvalid1() {
        $formField = new CM_FormField_Color();
        $environment = new CM_Frontend_Environment();
        $formField->validate($environment, '#12345G');
    }

    /**
     * @expectedException CM_Exception_FormFieldValidation
     */
    public function testValidateInvalid2() {
        $formField = new CM_FormField_Color();
        $environment = new CM_Frontend_Environment();
        $formField->validate($environment, '#123');
    }
}
