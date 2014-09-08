<?php

class CM_FormField_EmailTest extends CMTest_TestCase {

    public function testValidate() {
        $field = new CM_FormField_Email(['name' => 'email']);
        $environment = new CM_Frontend_Environment();

        $this->assertSame('test@example.com', $field->validate($environment, 'test@example.com'));
        try {
            $field->validate($environment, 'test(at)example.com');
            $this->fail('Invalid email address passed validation');
        } catch (CM_Exception_FormFieldValidation $e) {
            $this->assertTrue(true);
        }
    }

    public function testEmailVerification() {
        $emailVerificationMock = $this->getMock('CM_Service_EmailVerification_Standard', ['isValid']);
        $emailVerificationMock->expects($this->once())->method('isValid')->with('test(at)example.com')->will($this->returnValue(true));
        $field = new CM_FormField_Email(['name' => 'email', 'email-verification' => $emailVerificationMock]);
        $environment = new CM_Frontend_Environment();

        $this->assertSame('test(at)example.com', $field->validate($environment, 'test(at)example.com'));
    }
}
