<?php

class CM_ExceptionHandling_Handler_Cli extends CM_ExceptionHandling_Handler_Abstract {

    protected function _printException(Exception $exception) {
        $output = $this->_getOutput();
        $formatter = new CM_ExceptionHandling_Formatter_Plain();
        $output->writeln($formatter->formatException($exception));
    }

    /**
     * @return CM_OutputStream_Interface
     */
    protected function _getOutput() {
        return null === $this->_output ? new CM_OutputStream_Stream_StandardError() : $this->_output;
    }
}
