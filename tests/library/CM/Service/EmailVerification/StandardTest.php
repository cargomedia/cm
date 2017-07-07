<?php

class CM_Service_EmailVerification_StandardTest extends CMTest_TestCase {

    public function testEmpty() {
        $emailVerificationService = new CM_Service_EmailVerification_Standard();
        $this->assertFalse($emailVerificationService->isValid(''));
    }

    public function testMalformed() {
        $emailVerificationService = new CM_Service_EmailVerification_Standard();
        $this->assertFalse($emailVerificationService->isValid('invalid email@example.com'));
    }

    public function testHostUnresolved() {
        $emailVerificationService = new CM_Service_EmailVerification_Standard();
        $this->_mockHasMXRecords(false);
        $this->assertFalse($emailVerificationService->isValid('email@example.c'));
    }

    public function testMissingMX() {
        $emailVerificationService = new CM_Service_EmailVerification_Standard();
        $this->_mockHasMXRecords(false);
        $this->assertFalse($emailVerificationService->isValid('email@example.com'));
    }

    public function testValid() {
        $emailVerificationService = new CM_Service_EmailVerification_Standard();
        $this->_mockHasMXRecords(true);
        $this->assertTrue($emailVerificationService->isValid('email@example.com'));
    }

    protected function _mockHasMXRecords($value) {
        $networkToolsMockClass = $this->mockClass(CM_Service_NetworkTools::class)->newInstanceWithoutConstructor();
        $networkToolsMockClass->mockMethod('hasMXRecords')->set($value);
        $this->getServiceManager()->replaceInstance('network-tools', $networkToolsMockClass);
    }
}
