<?php

class CM_Log_Context {

    /** @var CM_Log_Context_ComputerInfo|null */
    private $_computerInfo;

    /** @var CM_Http_Request_Abstract|null */
    private $_httpRequest;

    /** @var CM_Log_Context_App */
    private $_appContext;

    /**
     * @param CM_Http_Request_Abstract|null    $httpRequest
     * @param CM_Log_Context_ComputerInfo|null $computerInfo
     * @param CM_Log_Context_App|null          $appContext
     */
    public function __construct(CM_Http_Request_Abstract $httpRequest = null,
                                CM_Log_Context_ComputerInfo $computerInfo = null,
                                CM_Log_Context_App $appContext = null) {

        $this->_httpRequest = $httpRequest;
        $this->_computerInfo = $computerInfo;
        if (null === $appContext) {
            $appContext = new CM_Log_Context_App();
        }
        $this->_appContext = $appContext;
    }

    /**
     * @return CM_Log_Context_ComputerInfo|null
     */
    public function getComputerInfo() {
        return $this->_computerInfo;
    }

    /**
     * @param CM_Http_Request_Abstract|null $httpRequest
     */
    public function setHttpRequest($httpRequest) {
        $this->_httpRequest = $httpRequest;
    }

    /**
     * @return CM_Http_Request_Abstract|null
     */
    public function getHttpRequest() {
        return $this->_httpRequest;
    }

    /**
     * @return CM_Log_Context_App
     */
    public function getAppContext() {
        return $this->_appContext;
    }

    /**
     * @return CM_Model_User|null
     */
    public function getUser() {
        return $this->getAppContext()->getUser();
    }

    /**
     * @return array
     */
    public function getExtra() {
        return $this->getAppContext()->getExtra();
    }

    public function __clone() {
        $this->_computerInfo = clone $this->getComputerInfo();
        $this->_appContext = clone $this->getAppContext();
    }
}
