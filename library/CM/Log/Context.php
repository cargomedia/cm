<?php

class CM_Log_Context {

    /** @var CM_Log_Context_ComputerInfo */
    private $_computerInfo;

    /** @var CM_Model_User|null */
    private $_user;

    /** @var CM_Http_Request_Abstract|null */
    private $_httpRequest;

    /** @var string[] */
    private $_extra = [];

    /**
     * @param array|null $options ['user' => CM_Model_User|null, 'http-request' => CM_Http_Request_Abstract|null, 'extra' => []|null]
     */
    public function __construct(array $options = null) {
        $options = array_merge([
            'user'         => null,
            'http-request' => null,
            'extra'        => null,
        ], $options ?: []);

        $this->_setComputerInfo();
        $this->_setUser($options['user']);
        $this->_setHttpRequest($options['http-request']);
        $this->_setExtra($options['extra']);
    }

    /**
     * @return CM_Log_Context_ComputerInfo
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

    private function _setComputerInfo() {
        $this->_computerInfo = new CM_Log_Context_ComputerInfo();
    }

    /**
     * @param CM_Model_User|null $user
     */
    private function _setUser(CM_Model_User $user = null) {
        $this->_user = $user;
    }

    /**
     * @param CM_Http_Request_Abstract|null $httpRequest
     */
    private function _setHttpRequest(CM_Http_Request_Abstract $httpRequest = null) {
        $this->_httpRequest = $httpRequest;
    }

    /**
     * @param array|null $extra
     */
    private function _setExtra(array $extra = null) {
        $this->_extra = $extra ?: [];
    }
}
