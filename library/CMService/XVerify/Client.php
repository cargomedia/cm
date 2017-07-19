<?php

class CMService_XVerify_Client extends CM_Service_EmailVerification_Standard {

    /** @var string */
    protected $_domain, $_code;

    /** @var int */
    protected $_timeout;

    /**
     * @param string   $domain
     * @param string   $code
     * @param int|null $timeout
     */
    public function __construct($domain, $code, $timeout = null) {
        $this->_domain = (string) $domain;
        $this->_code = (string) $code;
        $this->_timeout = (int) $timeout;
    }

    public function isValid($email) {
        $email = (string) $email;
        if (!parent::isValid($email)) {
            return false;
        }
        $key = __METHOD__ . '_email:' . $email;
        $cache = CM_Cache_Shared::getInstance();
        if (false === ($isValid = $cache->get($key))) {
            $response = $this->_getResponseBody($email);
            if (null === $response) {
                return true;
            }
            if (!isset($response['status']) || 'invalid' === $response['status'] || 'bad_data' === $response['status']) {
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
     * @return string
     */
    protected function _getDomain() {
        return $this->_domain;
    }

    /**
     * @param string $email
     * @return \Psr\Http\Message\ResponseInterface
     * @throws Exception
     */
    protected function _getResponse($email) {
        $email = (string) $email;
        $client = new \GuzzleHttp\Client(['base_uri' => 'http://www.xverify.com']);
        $parameterList = array('email' => $email, 'type' => 'json', 'domain' => $this->_getDomain(), 'apikey' => $this->_getCode());
        $url = '/services/emails/verify/?' . http_build_query($parameterList, '', '&', PHP_QUERY_RFC3986);
        $options = [\GuzzleHttp\RequestOptions::TIMEOUT => $this->_timeout];
        return $client->get($url, $options);
    }

    /**
     * @param string $email
     * @return array|null
     */
    protected function _getResponseBody($email) {
        try {
            $response = $this->_getResponse($email);
            try {
                $body = CM_Params::jsonDecode($response->getBody());
            } catch (CM_Exception_Invalid $exception) {
                $body = null;
            }
            $responseCode = isset($body['email']['responsecode']) ? (int) $body['email']['responsecode'] : null;
            $responseCodeInvalidList = array(
                503 => 'Invalid API Key/Service Not Active',
            );
            if (null === $responseCode || isset($responseCodeInvalidList[$responseCode])) {
                throw new CM_Exception('Invalid XVerify email validation response', null, [
                    'email'   => $email,
                    'code'    => $response->getStatusCode(),
                    'headers' => $response->getHeaders(),
                    'body'    => (string) $response->getBody(),
                ]);
            }
        } catch (Exception $exception) {
            $this->_handleException($exception);
            return null;
        }
        return $body['email'];
    }

    /**
     * @param Exception $exception
     */
    protected function _handleException(Exception $exception) {
        $logLevel = CM_Log_Logger::exceptionToLevel($exception);
        $context = new CM_Log_Context();
        $context->setException($exception);
        CM_Service_Manager::getInstance()->getLogger()->addMessage('XVerify client error', $logLevel, $context);
    }
}
