<?php

class CM_Log_Handler_Factory implements CM_Service_ManagerAwareInterface {

    use CM_Service_ManagerAwareTrait;

    /**
     * @param string|null $formatMessage
     * @param string|null $formatDate
     * @param int|null    $level
     * @param bool|null   $stopPropagation
     * @return CM_Log_Handler_Stream
     */
    public function createStderrHandler($formatMessage = null, $formatDate = null, $level = null, $stopPropagation = null) {
        $stream = new CM_OutputStream_Stream_StandardError();
        return $this->_createOutputStreamHandler($stream, $formatMessage, $formatDate, $level, $stopPropagation);
    }

    /**
     * @param string      $path
     * @param string|null $formatMessage
     * @param string|null $formatDate
     * @param int|null    $level
     * @param bool|null   $stopPropagation
     * @return CM_Log_Handler_Stream
     */
    public function createFileHandler($path, $formatMessage = null, $formatDate = null, $level = null, $stopPropagation = null) {
        $path = (string) $path;
        $filesystem = $this->getServiceManager()->getFilesystems()->getData();
        $file = new CM_File($path, $filesystem);
        $stream = new CM_OutputStream_File($file);
        return $this->_createOutputStreamHandler($stream, $formatMessage, $formatDate, $level, $stopPropagation);
    }

    /**
     * @param CM_OutputStream_Interface $stream
     * @param string|null               $formatMessage
     * @param string|null               $formatDate
     * @param int|null                  $level
     * @param bool|null                 $stopPropagation
     * @return CM_Log_Handler_Stream
     */
    protected function _createOutputStreamHandler(CM_OutputStream_Interface $stream, $formatMessage = null, $formatDate = null, $level = null, $stopPropagation = null) {
        $formatMessage = null !== $formatMessage ? (string) $formatMessage : $formatMessage;
        $formatDate = null !== $formatDate ? (string) $formatDate : $formatDate;
        $level = null !== $level ? (int) $level : $level;
        $stopPropagation = null !== $stopPropagation ? (bool) $stopPropagation : null;

        $formatter = new CM_Log_Formatter_Text($formatMessage, $formatDate);
        return new CM_Log_Handler_Stream($stream, $formatter, $level, $stopPropagation);
    }
}
