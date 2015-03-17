<?php

class bla extends CM_Process_WorkloadResult {

}

class CM_Process_WorkloadResult {

    /** @var mixed */
    private $_result;

    /** @var CM_ExceptionHandling_SerializableException|null */
    private $_exception;

    /**
     * @return mixed
     */
    public function getResult() {
        return $this->_result;
    }

    /**
     * @param mixed $result
     * @return $this
     */
    public function setResult($result) {
        $this->_result = $result;
        return $this;
    }

    /**
     * @return CM_ExceptionHandling_SerializableException|null
     */
    public function getException() {
        return $this->_exception;
    }

    /**
     * @param Exception $exception
     * @return static
     */
    public function setException(Exception $exception) {
        $this->_exception = new CM_ExceptionHandling_SerializableException($exception);
        return $this;
    }

    /**
     * @return bool
     */
    public function isSuccess() {
        return null === $this->getException();
    }
}
