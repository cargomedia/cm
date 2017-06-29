<?php

class CM_FormField_EmailTest extends CMTest_TestCase {

    public function testValidate() {
        $field = new CM_FormField_Email(['name' => 'email']);
        $environment = new CM_Frontend_Environment();

        $this->assertSame('test@google.com', $field->validate($environment, 'test@google.com'));
        try {
            $field->validate($environment, 'test(at)google.com');
            $this->fail('Invalid email address passed validation');
        } catch (CM_Exception_FormFieldValidation $e) {
            $this->assertTrue(true);
        }
    }

    public function testDisableEmailVerification() {
        $mockBuilder = $this->getMockBuilder('CM_Service_EmailVerification_Standard');
        $mockBuilder->setMethods(['isValid']);
        $emailVerificationMock = $mockBuilder->getMock();
        $emailVerificationMock->expects($this->never())->method('isValid');
        $serviceManager = CM_Service_Manager::getInstance();
        $emailVerificationDefault = $serviceManager->get('email-verification');
        $serviceManager->unregister('email-verification');
        $serviceManager->registerInstance('email-verification', $emailVerificationMock);
        $field = new CM_FormField_Email(['name' => 'email', 'disable-email-verification' => true]);
        $environment = new CM_Frontend_Environment();

        $this->assertSame('test@google.com', $field->validate($environment, 'test@google.com'));

        $serviceManager->unregister('email-verification');
        $serviceManager->registerInstance('email-verification', $emailVerificationDefault);
    }
}
