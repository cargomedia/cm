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

    public function testValid() {
        $emailVerificationService = new CM_Service_EmailVerification_Standard();
        $this->assertTrue($emailVerificationService->isValid('email@example.com'));
    }
}
