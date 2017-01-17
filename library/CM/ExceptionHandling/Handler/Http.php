<?php

class CM_ExceptionHandling_Handler_Http extends CM_ExceptionHandling_Handler_Abstract {

    protected function _printException(Exception $exception) {
        $output = $this->_getOutput();
        $formatter = new CM_ExceptionHandling_Formatter_Html();
        if (!headers_sent()) {
            header('HTTP/1.1 500 Internal Server Error');
            header('Content-Type: text/html');
        }
        if (!CM_Bootloader::getInstance()->isDebug()) {
            $output->writeln('Internal server error');
        } else {
            $output->writeln($formatter->formatException($exception));
        }
    }

    /**
     * @return CM_OutputStream_Interface
     */
    protected function _getOutput() {
        return null === $this->_output ? new CM_OutputStream_Stream_Output() : $this->_output;
    }
}
