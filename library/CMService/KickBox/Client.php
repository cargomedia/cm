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
        $key = __METHOD__ . '_email:' . $email .
            '_invalid:' . $this->_disallowInvalid .
            '_disposable:' . $this->_disallowDisposable .
            '_threshold:' . $this->_disallowUnknownThreshold;
        $cache = CM_Cache_Shared::getInstance();
        if (false === ($isValid = $cache->get($key))) {
            $response = $this->_getResponse($email);
            if (null === $response) {
                return true;
            }
            if (
                ($this->_disallowInvalid && 'invalid' === $response['result']) ||
                ($this->_disallowDisposable && 'true' === $response['disposable']) ||
                ($response['sendex'] < $this->_disallowUnknownThreshold && ('true' === $response['accept_all'] || 'unknown' === $response['result']))
            ) {
                $isValid = 0;
            } else {
                $isValid = 1;
            }
            $cache->set($key, $isValid);
        }
        return (bool) $isValid;
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
        if ($response->code !== 200 || !is_array($response->body)) {
            $exception = new CM_Exception('KickBox exception', array(
                'code'    => $response->code,
                'headers' => $response->headers,
                'body'    => $response->body,
            ));
            $exception->setSeverity(CM_Exception::WARN);
            $this->_handleException($exception);
            return null;
        }
        return $response->body;
    }

    /**
     * @param CM_Exception $exception
     */
    protected function _handleException(CM_Exception $exception) {
        CM_Bootloader::getInstance()->getExceptionHandler()->handleException($exception);
    }
}
