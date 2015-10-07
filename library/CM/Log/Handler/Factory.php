<?php

class CM_Log_Handler_Factory implements CM_Service_ManagerAwareInterface {

    use CM_Service_ManagerAwareTrait;

    /**
     * @param int|null  $level
     * @param bool|null $bubble
     * @return CM_Log_Handler_Newrelic
     */
    public function createNewrelicHandler($level = null, $bubble = null) {
        $level = null !== $level ? (int) $level : $level;
        $bubble = null !== $bubble ? (bool) $bubble : $bubble;
        $newrelic = $this->getServiceManager()->getNewrelic();
        return new CM_Log_Handler_Newrelic($newrelic, $level, $bubble);
    }

    /**
     * @param string      $streamClass
     * @param array|null  $streamArguments
     * @param string|null $formatMessage
     * @param string|null $formatDate
     * @param int|null    $level
     * @param bool|null   $bubble
     * @return CM_Log_Handler_Stream
     */
    public function createStreamHandler($streamClass, array $streamArguments = null, $formatMessage = null, $formatDate = null, $level = null, $bubble = null) {
        $streamClass = new ReflectionClass($streamClass);
        $streamArguments = null !== $streamArguments ? (array) $streamArguments : [];
        /** @var CM_OutputStream_Interface $stream */
        $stream = $streamClass->newInstanceArgs($streamArguments);
        return $this->_createOutputStreamHandler($stream, $formatMessage, $formatDate, $level, $bubble);
    }

    /**
     * @param string      $path
     * @param string|null $formatMessage
     * @param string|null $formatDate
     * @param int|null    $level
     * @param bool|null   $bubble
     * @return CM_Log_Handler_Stream
     */
    public function createFileHandler($path, $formatMessage = null, $formatDate = null, $level = null, $bubble = null) {
        $path = (string) $path;
        $filesystem = $this->getServiceManager()->getFilesystems()->getData();
        $file = new CM_File($path, $filesystem);
        $stream = new CM_OutputStream_File($file);
        return $this->_createOutputStreamHandler($stream, $formatMessage, $formatDate, $level, $bubble);
    }

    /**
     * @param CM_OutputStream_Interface $stream
     * @param string|null               $formatMessage
     * @param string|null               $formatDate
     * @param int|null                  $level
     * @param bool|null                 $bubble
     * @return CM_Log_Handler_Stream
     */
    protected function _createOutputStreamHandler(CM_OutputStream_Interface $stream, $formatMessage = null, $formatDate = null, $level = null, $bubble = null) {
        $level = null !== $level ? (int) $level : $level;
        $bubble = null !== $bubble ? (bool) $bubble : $bubble;

        $formatMessage = null !== $formatMessage ? (string) $formatMessage : $formatMessage;
        $formatDate = null !== $formatDate ? (string) $formatDate : $formatDate;
        $formatter = new CM_Log_Formatter_Text($formatMessage, $formatDate);
        return new CM_Log_Handler_Stream($stream, $formatter, $level, $bubble);
    }
}
