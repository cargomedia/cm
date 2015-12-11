<?php

class CM_Util_EncryptionTest extends CMTest_TestCase {

    public function testInvalidKeyFailing() {
        $encryption = new CM_Util_Encryption();

        $exception = $this->catchException(function () use ($encryption) {
            $encryption->encrypt('foo', 'BadKey');
        });
        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        $this->assertSame('Invalid key size', $exception->getMessage());

        $exception = $this->catchException(function () use ($encryption) {
            $encryption->decrypt('bar', 'BadKey');
        });
        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        $this->assertSame('Invalid key size', $exception->getMessage());
    }

    public function testEncryptDecrypt() {
        $encryptionKey = '!@#12345AbCdE901';
        $plain = 'highlySecretData';
        $encryption = new CM_Util_Encryption();
        $encrypted = $encryption->encrypt($plain, $encryptionKey);
        $this->assertNotEquals($plain, $encryption->decrypt($encrypted, str_replace('1', '2', $encryptionKey)));
        $this->assertSame($plain, $encryption->decrypt($encrypted, $encryptionKey));
    }

    public function testUrlSafeEncryption() {
        $encryptionKey = 'aaaabaaaaaaaaaab';
        $plain = '{"user":2,"expiration":1449847146}';
        $encryption = new CM_Util_Encryption();
        $encrypted = $encryption->encrypt($plain, $encryptionKey);
        $this->assertTrue(!preg_match('!/|\+|=!', $encrypted), 'Not url-safe');
    }
}
