<?php

class CM_FormField_CaptchaTest extends CMTest_TestCase {


    /**
     * @expectedException CM_Exception_FormFieldValidation
     */
    public function testParseUserInputIncomplete() {
        $formField = new CM_FormField_Captcha();
        $formField->parseUserInput(array('id' => 123));
    }

    /**
     * @expectedException CM_Exception_FormFieldValidation
     */
    public function testParseUserInputInvalid() {
        $formField = new CM_FormField_Captcha();
        $formField->parseUserInput('foo');
    }

    public function testValidate() {
        $captcha = CM_Captcha::create();
        $formField = new CM_FormField_Captcha();
        $environment = new CM_Frontend_Environment();
        $formField->validate($environment, array('id' => $captcha->getId(), 'value' => $captcha->getText()));
    }

    /**
     * @expectedException CM_Exception_FormFieldValidation
     */
    public function testValidateInvalidValue() {
        $captcha = CM_Captcha::create();
        $formField = new CM_FormField_Captcha();
        $environment = new CM_Frontend_Environment();
        $formField->validate($environment, array('id' => $captcha->getId(), 'value' => 'foo'));
    }

    /**
     * @expectedException CM_Exception_FormFieldValidation
     */
    public function testValidateInvalidCaptchaId() {
        $formField = new CM_FormField_Captcha();
        $environment = new CM_Frontend_Environment();
        $formField->validate($environment, array('id' => -1, 'value' => 'foo'));
    }
}
