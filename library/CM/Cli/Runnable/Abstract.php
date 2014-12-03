<?php

abstract class CM_Cli_Runnable_Abstract {

    /** @var CM_App|null */
    private $_application;

    /** @var CM_InputStream_Interface */
    private $_streamInput;

    /** @var CM_OutputStream_Interface */
    private $_streamOutput, $_streamError;

    /**
     * @param CM_InputStream_Interface|null  $streamInput
     * @param CM_OutputStream_Interface|null $streamOutput
     * @param CM_OutputStream_Interface|null $streamError
     */
    public function __construct(CM_InputStream_Interface $streamInput = null, CM_OutputStream_Interface $streamOutput = null, CM_OutputStream_Interface $streamError = null) {

        if (null === $streamInput) {
            $streamInput = new CM_InputStream_Null();
        }
        $this->_streamInput = $streamInput;
        if (null === $streamOutput) {
            $streamOutput = new CM_OutputStream_Null();
        }
        $this->_streamOutput = $streamOutput;
        if (null === $streamError) {
            $streamError = new CM_OutputStream_Null();
        }
        $this->_streamError = $streamError;
        $this->_initialize();
    }

    /**
     * @param CM_App|null $application
     */
    public function setApplication(CM_App $application = null) {
        $this->_application = $application;
    }

    protected function _initialize() {
    }

    /**
     * @return CM_App|null
     */
    protected function _getApplication() {
        return $this->_application;
    }

    /**
     * @return CM_OutputStream_Interface
     */
    protected function _getStreamError() {
        return $this->_streamError;
    }

    /**
     * @return CM_InputStream_Interface
     */
    protected function _getStreamInput() {
        return $this->_streamInput;
    }

    /**
     * @return CM_OutputStream_Interface
     */
    protected function _getStreamOutput() {
        return $this->_streamOutput;
    }

    /**
     * @throws CM_Exception_NotImplemented
     * @return string
     */
    public static function getPackageName() {
        throw new CM_Exception_NotImplemented('Package `' . get_called_class() . '` has no `getPackageName` implemented.');
    }
}
