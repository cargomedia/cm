<?php

class CM_Util_UrlEncryptor {

    /**
     * @param string $data
     * @param string $encryptionKey
     * @return string
     */
    public function encrypt($data, $encryptionKey) {
        return base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $encryptionKey, $data, MCRYPT_MODE_ECB));
    }

    /**
     * @param string $data
     * @param string $encryptionKey
     * @return string
     */
    public function decrypt($data, $encryptionKey) {
        return rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $encryptionKey, base64_decode($data), MCRYPT_MODE_ECB), "\0");
    }

}
