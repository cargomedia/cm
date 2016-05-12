<?php

abstract class CM_Http_Response_Abstract extends CM_Class_Abstract implements CM_Service_ManagerAwareInterface {

    use CM_Service_ManagerAwareTrait;

    /** @var CM_Http_Request_Abstract */
    protected $_request;

    /** @var CM_Frontend_Render */
    private $_render = null;

    /** @var CM_Site_Abstract */
    protected $_site = null;

    /** @var array */
    private $_headers = array();

    /** @var array */
    private $_rawHeaders = array();

    /** @var array */
    private $_cookies = array();

    /** @var null|string */
    private $_content = null;

    /** @var string|null */
    private $_stringRepresentation;

    /**
     * @param CM_Http_Request_Abstract $request
     * @param CM_Service_Manager       $serviceManager
     */
    public function __construct(CM_Http_Request_Abstract $request, CM_Service_Manager $serviceManager) {
        $this->_request = clone $request;
        $responseType = $this->_request->popPathPart();
        $language = $this->_request->popPathLanguage();
        $this->_site = $this->_request->popPathSite();

        $this->setServiceManager($serviceManager);
    }

    abstract protected function _process();

    public function process() {
        $this->getServiceManager()->getLogger()->getContext()->getAppContext()->setUserWithClosure(function () {
            return $this->getViewer();
        });
        $this->_process();

        if ($this->getRequest()->hasSession()) {
            $session = $this->getRequest()->getSession();
            if (!$session->isEmpty()) {
                $sessionExpiration = $session->hasLifetime() ? time() + $session->getLifetime() : null;
                $this->setCookie('sessionId', $session->getId(), $sessionExpiration);
            } elseif ($this->getRequest()->getCookie('sessionId')) {
                $this->deleteCookie('sessionId');
            }
        }

        if ($this->getRequest()->hasClientId()) {
            $requestClientId = $this->getRequest()->getClientId();
            if ($this->getRequest()->getCookie('clientId') != $requestClientId) {
                $this->setCookie('clientId', (string) $requestClientId, time() + (20 * 365 * 24 * 60 * 60));
            }
        }

        $name = $this->_getStringRepresentation();
        $this->getServiceManager()->getNewrelic()->setNameTransaction($name);
    }

    /**
     * @return CM_Http_Request_Abstract
     */
    public function getRequest() {
        return $this->_request;
    }

    /**
     * @return CM_Site_Abstract
     */
    public function getSite() {
        return $this->_site;
    }

    /**
     * @param bool $needed OPTIONAL Throw an CM_Exception_AuthRequired if not authenticated
     * @return CM_Model_User|null
     * @throws CM_Exception_AuthRequired
     */
    public function getViewer($needed = false) {
        return $this->getRequest()->getViewer($needed);
    }

    /**
     * @return string|null
     */
    public function getContent() {
        return $this->_content;
    }

    /**
     * @return CM_Frontend_Render
     */
    public function getRender() {
        if (!$this->_render) {
            $languageRewrite = !$this->getViewer() && $this->getRequest()->getLanguageUrl();
            $environment = $this->getEnvironment();
            $this->_render = new CM_Frontend_Render($environment, $languageRewrite, $this->getServiceManager());
        }
        return $this->_render;
    }

    /**
     * @return CM_Frontend_Environment
     * @throws CM_Exception_AuthRequired
     */
    public function getEnvironment() {
        $request = $this->getRequest();
        $location = $request->getLocation();
        $viewer = $request->getViewer();
        $currency = null;
        if (null === $currency && null !== $viewer) {
            $currency = $viewer->getCurrency();
        }
        if (null === $currency && null !== $location) {
            $currency = CM_Model_Currency::findByLocation($location);
        }
        $clientDevice = new CM_Http_ClientDevice($request);
        
        $timeZone = $request->getTimeZone();
        if (null === $timeZone && null !== $location) {
            $timeZone = $location->getTimeZone();
        }
        
        return new CM_Frontend_Environment($this->getSite(), $viewer, $request->getLanguage(), $timeZone, null, $location, $currency, $clientDevice);
    }

    /**
     * @return array
     */
    public function getCookies() {
        return $this->_cookies;
    }

    /**
     * @return array
     */
    public function getHeaders() {
        $headers = $this->_rawHeaders;
        foreach ($this->_headers as $key => $value) {
            $headers[] = $key . ': ' . $value;
        }
        foreach ($this->_cookies as $name => $cookieParameters) {
            $cookie = 'Set-Cookie: ' . $name . '=' . urlencode($cookieParameters['value']);
            if (null !== $cookieParameters['expire']) {
                $cookie .= '; Expires=' . date('D\, d\-M\-Y h:i:s e', (int) $cookieParameters['expire']);
            }
            $cookie .= '; Path=' . $cookieParameters['path'];

            $headers[] = $cookie;
        }

        return $headers;
    }

    /**
     * Sets not found header (can be server specific)
     */
    public function setHeaderNotfound() {
        $this->addHeaderRaw('HTTP/1.0 404 Not Found');
    }

    public function setHeaderDisableCache() {
        # Via http://stackoverflow.com/questions/49547/making-sure-a-web-page-is-not-cached-across-all-browsers/5493543#5493543
        $this->setHeader('Cache-Control', 'no-store, must-revalidate');
    }

    /**
     * @param string $key
     * @param string $value
     */
    public function setHeader($key, $value) {
        $this->_headers[$key] = $value;
    }

    /**
     * @param string      $name
     * @param string      $value
     * @param int         $expire
     * @param string|null $path
     */
    public function setCookie($name, $value, $expire = null, $path = null) {
        if (null === $path) {
            $path = '/';
        }
        if (null !== $expire) {
            $expire = (int) $expire;
        }
        $name = (string) $name;
        $value = (string) $value;
        $path = (string) $path;

        $this->_cookies[$name] = array('value' => $value, 'expire' => $expire, 'path' => $path);
    }

    /**
     * @param string $header
     */
    public function addHeaderRaw($header) {
        $this->_rawHeaders[] = $header;
    }

    /**
     * @param string $name
     */
    public function deleteCookie($name) {
        $this->setCookie($name, '', 1);
    }

    /**
     * Processes all headers and sends them
     */
    public function sendHeaders() {
        foreach ($this->getHeaders() as $header) {
            header($header, false);
        }
    }

    public function sendContent() {
        echo $this->getContent();
    }

    public function send() {
        $this->sendHeaders();
        $this->sendContent();
    }

    /**
     * @param string $content
     */
    protected function _setContent($content) {
        $this->_content = (string) $content;
    }

    /**
     * @param string $string
     */
    protected function _setStringRepresentation($string) {
        $this->_stringRepresentation = (string) $string;
    }

    /**
     * @return string
     */
    protected function _getStringRepresentation() {
        if (null === $this->_stringRepresentation) {
            return get_class($this);
        }
        return $this->_stringRepresentation;
    }

    /**
     * @param Closure $regularCode
     * @param Closure $errorCode
     * @return mixed
     * @throws CM_Exception
     */
    protected function _runWithCatching(Closure $regularCode, Closure $errorCode) {
        try {
            return $regularCode();
        } catch (CM_Exception $ex) {
            $config = self::_getConfig();
            $exceptionsToCatch = $config->exceptionsToCatch;
            $catchPublicExceptions = !empty($config->catchPublicExceptions);
            $errorOptions = \Functional\first($exceptionsToCatch, function ($options, $exceptionClass) use ($ex) {
                return is_a($ex, $exceptionClass);
            });
            $catchException = null !== $errorOptions;
            if ($catchException && isset($errorOptions['log']) && true === $errorOptions['log']) {
                $logLevel = isset($errorOptions['level']) ? $errorOptions['level'] : null;
                if (null === $logLevel) {
                    $logLevel = CM_Log_Logger::exceptionSeverityToLevel($ex);
                }
                $this->getServiceManager()->getLogger()->addMessage('HTML response error', $logLevel, new CM_Log_Context_App(null, $this->getViewer(), $ex));
            }
            if (!$catchException && ($catchPublicExceptions && $ex->isPublic())) {
                $errorOptions = [];
                $catchException = true;
            }
            if ($catchException) {
                return $errorCode($ex, $errorOptions);
            }
            throw $ex;
        }
    }

    /**
     * @param CM_Http_Request_Abstract $request
     * @return CM_Http_Response_Abstract|string
     */
    public static function getResponseClassName(CM_Http_Request_Abstract $request) {
        /** @var $responseClass CM_Http_Response_Abstract */
        foreach (array_reverse(self::getClassChildren()) as $responseClass) {
            if ($responseClass::match($request)) {
                return $responseClass;
            }
        }
        return 'CM_Http_Response_Page';
    }

    /**
     * @param CM_Http_Request_Abstract $request
     * @param CM_Service_Manager       $serviceManager
     * @return CM_Http_Response_Abstract
     */
    public static function factory(CM_Http_Request_Abstract $request, CM_Service_Manager $serviceManager) {
        $className = self::getResponseClassName($request);
        return new $className($request, $serviceManager);
    }

    /**
     * @param CM_Http_Request_Abstract $request
     * @return bool
     */
    public static function match(CM_Http_Request_Abstract $request) {
        return false;
    }
}
