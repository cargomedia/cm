<?php

class CM_Provision_UpdateScript {

    /** @var boolean */
    private $_blocking;

    /** @var callable */
    private $_body;

    /**
     * @param callable     $body
     * @param boolean|null $blocking
     */
    public function __construct(callable $body, $blocking = null) {
        $this->_body = $body;
        $this->_blocking = (boolean) $blocking;
    }

    /**
     * @return boolean
     */
    public function isBlocking() {
        return $this->_blocking;
    }

    public function run() {
        call_user_func($this->_body);
    }
}
