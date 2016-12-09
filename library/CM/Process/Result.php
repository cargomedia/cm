<?php

class CM_Process_Result {

    /** @var int|null */
    private $_returnCode;

    /**
     * @param int|null $returnCode
     */
    public function __construct($returnCode = null) {
        $this->setReturnCode($returnCode);
    }

    /**
     * @return int|null
     */
    public function getReturnCode() {
        return $this->_returnCode;
    }

    /**
     * @param int|null $code
     * @return $this
     */
    public function setReturnCode($code) {
        $this->_returnCode = (null !== $code) ? (int) $code : null;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSuccess() {
        return 0 === $this->_returnCode;
    }
}
