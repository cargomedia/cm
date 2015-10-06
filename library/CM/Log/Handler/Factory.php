<?php

class CM_Log_Handler_Factory implements CM_Service_ManagerAwareInterface {

    use CM_Service_ManagerAwareTrait;

    /**
     * @param string      $streamClass
     * @param array|null  $streamArguments
     * @param int|null    $level
     * @param string|null $formatMessage
     * @param string|null $formatDate
     * @return CM_Log_Handler_Stream
     */
    public function createStreamHandler($streamClass, array $streamArguments = null, $level = null, $formatMessage = null, $formatDate = null) {
        $streamClass = new ReflectionClass($streamClass);
        $streamArguments = null !== $streamArguments ? (array) $streamArguments : [];
        /** @var CM_OutputStream_Interface $stream */
        $stream = $streamClass->newInstanceArgs($streamArguments);
        return $this->_createOutputStreamHandler($stream, $level, $formatMessage, $formatDate);
    }

    /**
     * @param string      $path
     * @param int|null    $level
     * @param string|null $formatMessage
     * @param string|null $formatDate
     * @return CM_Log_Handler_Stream
     */
    public function createFileHandler($path, $level = null, $formatMessage = null, $formatDate = null) {
        $path = (string) $path;
        $filesystem = $this->getServiceManager()->getFilesystems()->getData();
        $file = new CM_File($path, $filesystem);
        $stream = new CM_OutputStream_File($file);
        return $this->_createOutputStreamHandler($stream, $level, $formatMessage, $formatDate);
    }

    /**
     * @param CM_OutputStream_Interface $stream
     * @param int|null                  $level
     * @param string|null               $formatMessage
     * @param string|null               $formatDate
     * @return CM_Log_Handler_Stream
     */
    protected function _createOutputStreamHandler(CM_OutputStream_Interface $stream, $level = null, $formatMessage = null, $formatDate = null) {
        $level = null !== $level ? (int) $level : $level;
        $formatMessage = null !== $formatMessage ? (string) $formatMessage : $formatMessage;
        $formatDate = null !== $formatDate ? (string) $formatDate : $formatDate;
        $formatter = new CM_Log_Formatter_Text($formatMessage, $formatDate);
        return new CM_Log_Handler_Stream($stream, $formatter, $level);
    }

}
