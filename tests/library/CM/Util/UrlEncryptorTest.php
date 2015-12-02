<?php

class CM_Util_UrlEncryptorTest extends CMTest_TestCase {

    public function testEncryptDecrypt() {
        $encryptionKey = 'h7GvKuoG7GuHmGkatf(UquBpoFwam';
        $plain = 'highlySecretData';

        $encryptor = new CM_Util_UrlEncryptor();
        $encrypted = $encryptor->encrypt($plain, $encryptionKey);
        $this->assertNotSame(false, base64_decode($encrypted, true), 'Encrypted data is not valid base64 string');
        $this->assertNotEquals($plain, $encryptor->decrypt($encrypted, $encryptionKey . '_'));
        $this->assertSame($plain, $encryptor->decrypt($encrypted, $encryptionKey));
    }
}
