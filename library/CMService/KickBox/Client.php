<?php

class CMService_KickBox_Client implements CM_Service_EmailVerification_ClientInterface {

    /** @var string */
    protected $_code;

    /**
     * @param string $code
     */
    public function __construct($code) {
        $this->_code = (string) $code;
    }

    public function isValid($email) {
        $email = (string) $email;
        if (false === filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        $response = $this->_getResponse($email);
        if ('invalid' === $response['result']) {
            return false;
        }
        if ('true' === $response['disposable']) {
            return false;
        }
        if ('true' === $response['accept_all'] && $response['sendex'] < 0.2) {
            return false;
        }
        return true;
    }

    /**
     * @return string
     */
    protected function _getCode() {
        return $this->_code;
    }

    /**
     * @param string $email
     * @return mixed
     */
    protected function _getResponse($email) {
        $kickBox = new \Kickbox\Client($this->_getCode());
        $response = $kickBox->kickbox()->verify($email);
        return CM_Params::jsonDecode($response->body);
    }
}
