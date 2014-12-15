<?php

class CM_Process_WorkloadResult {

    /** @var mixed */
    private $_result;

    /** @var CM_ExceptionHandling_SerializableException|null */
    private $_exception;

    /** @var int */
    private $_pid;

    /**
     * @param int            $pid
     * @param mixed          $result
     * @param Exception|null $exception
     */
    public function __construct($pid, $result, Exception $exception = null) {
        if (null !== $exception) {
            $exception = new CM_ExceptionHandling_SerializableException($exception);
        }
        $this->_result = $result;
        $this->_exception = $exception;
        $this->_pid = (int) $pid;
    }

    /**
     * @return int
     */
    public function getPid() {
        return $this->_pid;
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

    /**
     * @return bool
     */
    public function isSuccess() {
        return null === $this->getException();
    }
}
