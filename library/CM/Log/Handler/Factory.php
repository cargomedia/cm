<?php

class CM_Log_Handler_Factory {

    /**
     * @param string      $streamClass
     * @param array|null  $streamArguments
     * @param int|null    $level
     * @param string|null $format
     * @param string|null $date_format
     * @return CM_Log_Handler_Stream
     */
    public function createStreamHandler($streamClass, array $streamArguments = null, $level = null, $format = null, $date_format = null) {
        $streamClass = new ReflectionClass($streamClass);
        $streamArguments = null !== $streamArguments ? (array) $streamArguments : [];
        $stream = $streamClass->newInstanceArgs($streamArguments);
        return new CM_Log_Handler_Stream($stream, $level, $format, $date_format);
    }

    /**
     * @param string      $path
     * @param int|null    $level
     * @param string|null $format
     * @param string|null $date_format
     * @return CM_Log_Handler_Stream
     */
    public function createFileHandler($path, $level = null, $format = null, $date_format = null) {
        $path = (string) $path;
        $file = new CM_File($path);
        $stream = new CM_OutputStream_File($file);
        return $this->_createOutputStreamHandler($stream, $level, $format, $date_format);
    }

    /**
     * @param CM_OutputStream_Interface $stream
     * @param int|null                  $level
     * @param string|null               $format
     * @param string|null               $date_format
     * @return CM_Log_Handler_Stream
     */
    protected function _createOutputStreamHandler(CM_OutputStream_Interface $stream, $level = null, $format = null, $date_format = null) {
        $level = null !== $level ? (int) $level : $level;
        $format = null !== $format ? (string) $format : $format;
        $date_format = null !== $date_format ? (string) $date_format : $date_format;
        return new CM_Log_Handler_Stream($stream, $level, $format, $date_format);
    }

}
