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

    public function testEncryptDecryptUrl() {
        $encryptionKey = '!@#12345AbCdE901';
        $plain = 'highlySecretData';
        $encryption = new CM_Util_Encryption();
        $encrypted = $encryption->encryptUrl($plain, $encryptionKey);
        $this->assertNotSame(false, base64_decode($encrypted, true), 'Encrypted data is not valid base64 string');
        $this->assertNotEquals($plain, $encryption->decryptUrl($encrypted, str_replace('1', '2', $encryptionKey)));
        $this->assertSame($plain, $encryption->decryptUrl($encrypted, $encryptionKey));
    }
}
