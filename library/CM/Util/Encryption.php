<?php

class CM_Util_Encryption {

    /**
     * @param string $data
     * @param string $secretKey
     * @return string
     */
    public function encrypt($data, $secretKey) {
        $this->_validateKey($secretKey);
        return strtr(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $secretKey, $data, MCRYPT_MODE_ECB)), '+=/', '-_.');
    }

    /**
     * @param string $data
     * @param string $secretKey
     * @return string
     */
    public function decrypt($data, $secretKey) {
        $this->_validateKey($secretKey);
        return rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $secretKey, base64_decode(strtr($data, '-_.', '+=/')), MCRYPT_MODE_ECB), "\0");
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
