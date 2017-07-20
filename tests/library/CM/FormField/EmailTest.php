<?php

class CM_FormField_EmailTest extends CMTest_TestCase {

    public function testValidate() {
        $field = new CM_FormField_Email(['name' => 'email', 'enable-email-verification' => true]);
        $environment = new CM_Frontend_Environment();
        $this->_mockHasMXRecords(true);
        $this->assertSame('test@example.com', $field->validate($environment, 'test@example.com'));
        try {
            $this->_mockHasMXRecords(false);
            $field->validate($environment, 'test(at)example.com');
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
        $field = new CM_FormField_Email(['name' => 'email']);
        $environment = new CM_Frontend_Environment();
        $this->_mockHasMXRecords(true);

        $this->assertSame('test@example.com', $field->validate($environment, 'test@example.com'));

        $serviceManager->unregister('email-verification');
        $serviceManager->registerInstance('email-verification', $emailVerificationDefault);
    }

    protected function _mockHasMXRecords($value) {
        $networkToolsMockClass = $this->mockClass(CM_Service_NetworkTools::class)->newInstanceWithoutConstructor();
        $networkToolsMockClass->mockMethod('hasMXRecords')->set($value);
        $this->getServiceManager()->replaceInstance('network-tools', $networkToolsMockClass);
    }
}
