<?php

class CM_Util_Encryption {

    /**
     * @param string $data
     * @param string $secretKey
     * @return string
     */
    public function encryptUrl($data, $secretKey) {
        return base64_encode($this->encrypt($data, $secretKey));
    }

    /**
     * @param string $data
     * @param string $secretKey
     * @return string
     */
    public function decryptUrl($data, $secretKey) {
        return $this->decrypt(base64_decode($data), $secretKey);
    }

    /**
     * @param string $data
     * @param string $secretKey
     * @return string
     */
    public function encrypt($data, $secretKey) {
        $this->_validateKey($secretKey);
        return mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $secretKey, $data, MCRYPT_MODE_ECB);
    }

    /**
     * @param $data
     * @param $secretKey
     * @return string
     */
    public function decrypt($data, $secretKey) {
        $this->_validateKey($secretKey);
        return rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $secretKey, $data, MCRYPT_MODE_ECB), "\0");
    }

    /**
     * @param string $secretKey
     * @throws CM_Exception_Invalid
     */
    private function _validateKey($secretKey) {
        $keySize = strlen($secretKey);
        $validKeySizes = mcrypt_module_get_supported_key_sizes(MCRYPT_RIJNDAEL_128);
        if (!in_array($keySize, $validKeySizes, true)) {
            throw new CM_Exception_Invalid('Invalid key size');
        }
    }
}
