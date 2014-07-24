<?php

class CMService_KickBox_Client implements CM_Service_EmailVerification_ClientInterface {

    /** @var string */
    protected $_code;

    /** @var bool */
    protected $_disallowInvalid, $_disallowDisposable;

    /** @var float */
    protected $_disallowUnknownThreshold;

    /**
     * @param string $code
     * @param bool   $disallowInvalid
     * @param bool   $disallowDisposable
     * @param float  $disallowUnknownThreshold
     */
    public function __construct($code, $disallowInvalid, $disallowDisposable, $disallowUnknownThreshold) {
        $this->_code = (string) $code;
        $this->_disallowInvalid = (bool) $disallowInvalid;
        $this->_disallowDisposable = (bool) $disallowDisposable;
        $this->_disallowUnknownThreshold = (float) $disallowUnknownThreshold;
    }

    public function isValid($email) {
        $email = (string) $email;
        if (false === filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        $response = $this->_getResponse($email);
        if (null === $response) {
            return true;
        }
        if ($this->_disallowInvalid && 'invalid' === $response['result']) {
            return false;
        }
        if ($this->_disallowDisposable && 'true' === $response['disposable']) {
            return false;
        }
        if ($response['sendex'] < $this->_disallowUnknownThreshold && ('true' === $response['accept_all'] || 'unknown' === $response['result'])) {
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
     * @return array|null
     */
    protected function _getResponse($email) {
        $kickBox = new \Kickbox\Client($this->_getCode());
        $response = $kickBox->kickbox()->verify($email);
        if ($response->code !== 200) {
            return null;
        }
        return CM_Params::jsonDecode($response->body);
    }
}
