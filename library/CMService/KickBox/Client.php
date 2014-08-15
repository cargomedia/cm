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
            $response = $this->_getResponseBody($email);
            if (null === $response) {
                return true;
            }
            $isInvalid = isset($response['result']) && 'invalid' === $response['result'];
            $isDisposable = isset($response['disposable']) && 'true' === $response['disposable'];
            $isSendexUnderThreshold = isset($response['sendex']) && $response['sendex'] < $this->_disallowUnknownThreshold;
            $isUnknown = (isset($response['result']) && 'unknown' === $response['result']) ||
                (isset($response['accept_all']) && 'true' === $response['accept_all']);
            if (($this->_disallowInvalid && $isInvalid) || ($this->_disallowDisposable && $isDisposable) || ($isSendexUnderThreshold && $isUnknown)) {
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
     * @return \Kickbox\HttpClient\Response
     * @throws Exception
     */
    protected function _getResponse($email) {
        $kickBox = new \Kickbox\Client($this->_getCode());
        return $kickBox->kickbox()->verify($email);
    }

    /**
     * @param string $email
     * @return array|null
     */
    protected function _getResponseBody($email) {
        try {
            $response = $this->_getResponse($email);
            if ($response->code !== 200 || !is_array($response->body)) {
                $this->_logException(array(
                    'email'   => $email,
                    'code'    => $response->code,
                    'headers' => $response->headers,
                    'body'    => $response->body,
                ));
                return null;
            }
        } catch (Exception $exception) {
            $serializableException = new CM_ExceptionHandling_SerializableException($exception);
            $formatter = new CM_ExceptionHandling_Formatter_Plain();
            $this->_logException(array(
                'email'     => $email,
                'exception' => $formatter->getHeader($serializableException),
                'trace'     => $formatter->getTrace($serializableException),
            ));
            return null;
        }
        return $response->body;
    }

    /**
     * @param array $metaInfo
     */
    protected function _logException(array $metaInfo) {
        $exception = new CM_Exception('KickBox exception', $metaInfo);
        $exception->setSeverity(CM_Exception::WARN);
        CM_Bootloader::getInstance()->getExceptionHandler()->handleException($exception);
    }
}
