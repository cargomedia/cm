<?php

class CM_Clockwork_Event_Result {

    /** @var boolean|null */
    private $_success;

    /**
     * @return CM_Clockwork_Event_Result
     */
    public function setSuccess() {
        $this->_success = true;
        return $this;
    }

    /**
     * @return CM_Clockwork_Event_Result
     */
    public function setFailure() {
        $this->_success = false;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isSuccessful() {
        return true === $this->_success;
    }
}
