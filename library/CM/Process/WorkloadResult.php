<?php

class CM_Process_WorkloadResult {

    /** @var mixed */
    private $_result;

    /** @var CM_ExceptionHandling_SerializableException|null */
    private $_exception;

    /**
     * @param mixed          $result
     * @param Exception|null $exception
     */
    public function __construct($result, Exception $exception = null) {
        if (null !== $exception) {
            $exception = new CM_ExceptionHandling_SerializableException($exception);
        }
        $this->_result = $result;
        $this->_exception = $exception;
    }

    /**
     * @return mixed
     */
    public function getResult() {
        return $this->_result;
    }

    /**
     * @return CM_ExceptionHandling_SerializableException|null
     */
    public function getException() {
        return $this->_exception;
    }
}
