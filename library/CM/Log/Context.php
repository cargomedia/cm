<?php

class CM_Log_Context {

    /** @var CM_Log_Context_ComputerInfo|null */
    private $_computerInfo;

    /** @var CM_Http_Request_Abstract|null */
    private $_httpRequest;

    /** @var Closure|null */
    protected $_getUserClosure;

    /** @var Exception|null */
    private $_exception;

    /** @var array */
    private $_extra;

    public function __construct() {
        $this->_extra = [];
    }

    /**
     * @param CM_Log_Context_ComputerInfo $computerInfo
     */
    public function setComputerInfo(CM_Log_Context_ComputerInfo $computerInfo) {
        $this->_computerInfo = $computerInfo;
    }

    /**
     * @return CM_Log_Context_ComputerInfo
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
     * @param Closure $getUser
     */
    public function setUserWithClosure(Closure $getUser) {
        $this->_getUserClosure = $getUser;
    }

    /**
     * @param CM_Model_User|null $user
     */
    public function setUser(CM_Model_User $user = null) {
        $this->setUserWithClosure(function () use ($user) {
            return $user;
        });
    }

    /**
     * @return CM_Model_User|null
     * @throws CM_Exception_Invalid
     */
    public function getUser() {
        if (null === $this->_getUserClosure) {
            return null;
        }
        $user = call_user_func($this->_getUserClosure);
        if ($user === null || $user instanceof CM_Model_User) {
            return $user;
        }
        throw new CM_Exception_Invalid('User need to be CM_Model_User or null');
    }

    /**
     * @return Exception|null
     */
    public function getException() {
        return $this->_exception;
    }

    /**
     * @param Exception|null $exception
     * @return $this
     */
    public function setException($exception) {
        $this->_exception = $exception;
        return $this;
    }

    /**
     * @param array $extra
     * @return $this
     */
    public function setExtra(array $extra) {
        $this->_extra = $extra;
        return $this;
    }

    /**
     * @return array
     */
    public function getExtra() {
        return $this->_extra;
    }

    public function __clone() {
        if (null !== $this->_computerInfo) {
            $this->_computerInfo = clone $this->_computerInfo;
        }
    }

    /**
     * @param CM_Log_Context $context
     */
    public function merge(CM_Log_Context $context) {
        if ($computerInfo = $context->getComputerInfo()) {
            $this->setComputerInfo($computerInfo);
        }

        if ($httpRequest = $context->getHttpRequest()) {
            $this->setHttpRequest($httpRequest);
        }

        if ($getUserClosure = $context->_getUserClosure) {
            $this->setUserWithClosure($getUserClosure);
        }

        if ($exception = $context->getException()) {
            $this->setException($exception);
        }

        $extra = array_merge($this->getExtra(), $context->getExtra());
        $this->setExtra($extra);
    }
}
