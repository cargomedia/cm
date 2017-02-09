<?php

class CM_Clockwork_Event_Result {

    /** @var boolean */
    private $_success;

    public function setSuccess() {
        $this->_success = true;
    }

    public function setFailure() {
        $this->_success = false;
    }

    /**
     * @return boolean
     */
    public function isSuccessful() {
        return true === $this->_success;
    }
}
