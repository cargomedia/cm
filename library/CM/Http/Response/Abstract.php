<?php

use CM\Url\AppUrl;

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
     * @param CM_Site_Abstract         $site
     * @param CM_Service_Manager       $serviceManager
     */
    public function __construct(CM_Http_Request_Abstract $request, CM_Site_Abstract $site, CM_Service_Manager $serviceManager) {
        $this->setServiceManager($serviceManager);
        $this->_request = $request;
        $this->_site = $site;
    }

    abstract protected function _process();

    public function process() {
        $this->getServiceManager()->getLogger()->getContext()->setUserWithClosure(function () {
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
     * @return AppUrl
     */
    public function getUrl() {
        $request = $this->getRequest();
        $url = AppUrl::createWithEnvironment($request->getPath(), $this->getRender()->getEnvironment());
        /** @var AppUrl $url */
        $url = $url->withParams($request->getQuery());
        return $url;
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
            $this->_render = $this->createRender();
        }
        return $this->_render;
    }

    /**
     * @return CM_Frontend_Render
     */
    public function createRender() {
        return new CM_Frontend_Render($this->getEnvironment(), $this->getServiceManager());
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
        return new CM_Frontend_Environment($this->getSite(), $viewer, $request->getLanguage(), $request->getTimeZone(), null, $location, $currency, $clientDevice);
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
     * @param int $maxAge
     */
    public function setHeaderExpires($maxAge) {
        $maxAge = (int) $maxAge;
        $this->setHeader('Cache-Control', 'max-age=' . $maxAge);
        $this->setHeader('Expires', gmdate('D, d M Y H:i:s \G\M\T', time() + $maxAge));
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
                    $logLevel = CM_Log_Logger::exceptionToLevel($ex);
                }
                $context = new CM_Log_Context();
                $context->setUser($this->getViewer());
                $context->setException($ex);
                $this->getServiceManager()->getLogger()->addMessage('Response processing error', $logLevel, $context);
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
     * @param CM_Site_Abstract         $site
     * @param CM_Service_Manager       $serviceManager
     * @return CM_Http_Response_Abstract|null
     */
    public static function createFromRequest(CM_Http_Request_Abstract $request, CM_Site_Abstract $site, CM_Service_Manager $serviceManager) {
        return null;
    }

    /**
     * @return bool
     */
    public static function catchAll() {
        return false;
    }

}
