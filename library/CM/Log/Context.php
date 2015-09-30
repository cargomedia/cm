<?php

class CM_Log_Context {

    /** @var CM_Log_Context_ComputerInfo|null */
    private $_computerInfo;

    /** @var CM_Model_User|null */
    private $_user;

    /** @var CM_Http_Request_Abstract|null */
    private $_httpRequest;

    /** @var array */
    private $_extra;

    /**
     * @param CM_Model_User|null               $user
     * @param CM_Http_Request_Abstract|null    $httpRequest
     * @param CM_Log_Context_ComputerInfo|null $computerInfo
     * @param array|null                       $extra
     */
    public function __construct(CM_Model_User $user = null,
                                CM_Http_Request_Abstract $httpRequest = null,
                                CM_Log_Context_ComputerInfo $computerInfo = null,
                                array $extra = null) {
        if (null === $extra) {
            $extra = [];
        }
        $this->_user = $user;
        $this->_httpRequest = $httpRequest;
        $this->_computerInfo = $computerInfo;
        $this->_extra = (array) $extra;
    }

    /**
     * @return CM_Log_Context_ComputerInfo|null
     */
    public function getComputerInfo() {
        return $this->_computerInfo;
    }

    /**
     * @return CM_Model_User|null
     */
    public function getUser() {
        return $this->_user;
    }

    /**
     * @return CM_Http_Request_Abstract|null
     */
    public function getHttpRequest() {
        return $this->_httpRequest;
    }

    /**
     * @return array
     */
    public function getExtra() {
        return $this->_extra;
    }

    /**
     * Overrides all properties not defined on the current instance
     *
     * @param CM_Log_Context $context
     * @return CM_Log_Context
     */
    public function merge(CM_Log_Context $context) {
        if (null === $this->getUser()) {
            $this->_user = $context->getUser();
        }
        if (null === $this->getHttpRequest()) {
            $this->_httpRequest = $context->getHttpRequest();
        }
        if (null === $this->getComputerInfo()) {
            $this->_computerInfo = $context->getComputerInfo();
        }
        $this->_extra = array_merge($context->getExtra(), $this->_extra);
        return $this;
    }
}
