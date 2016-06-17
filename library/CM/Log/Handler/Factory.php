<?php

class CM_Log_Handler_Factory implements CM_Service_ManagerAwareInterface {

    use CM_Service_ManagerAwareTrait;

    /**
     * @param string|null $formatMessage
     * @param string|null $formatDate
     * @param int|null    $minLevel
     * @return CM_Log_Handler_Stream
     */
    public function createStderrHandler($formatMessage = null, $formatDate = null, $minLevel = null) {
        $formatMessage = null !== $formatMessage ? (string) $formatMessage : $formatMessage;
        $formatDate = null !== $formatDate ? (string) $formatDate : $formatDate;

        $stream = new CM_OutputStream_Stream_StandardError();
        $formatter = new CM_Log_Formatter_Text($formatMessage, $formatDate);
        return $this->_createStreamHandler($stream, $formatter, $minLevel);
    }

    /**
     * @param string      $path
     * @param string|null $formatMessage
     * @param string|null $formatDate
     * @param int|null    $minLevel
     * @return CM_Log_Handler_Stream
     */
    public function createFileHandler($path, $formatMessage = null, $formatDate = null, $minLevel = null) {
        $path = (string) $path;
        $formatMessage = null !== $formatMessage ? (string) $formatMessage : $formatMessage;
        $formatDate = null !== $formatDate ? (string) $formatDate : $formatDate;

        $filesystem = $this->getServiceManager()->getFilesystems()->getData();
        $file = new CM_File($path, $filesystem);
        $file->ensureParentDirectory();
        $stream = new CM_OutputStream_File($file);
        $formatter = new CM_Log_Formatter_Text($formatMessage, $formatDate);
        return $this->_createStreamHandler($stream, $formatter, $minLevel);
    }

    /**
     * @param array[] $layersConfig
     * @return CM_Log_Handler_Layered
     * @throws CM_Exception_Invalid
     */
    public function createLayeredHandler($layersConfig) {
        $layeredHandler = new CM_Log_Handler_Layered();
        foreach ($layersConfig as $layerConfig) {
            $layer = new CM_Log_Handler_Layered_Layer();
            foreach ($layerConfig as $handlerServiceName) {
                $layer->addHandler($this->getServiceManager()->get($handlerServiceName, 'CM_Log_Handler_HandlerInterface'));
            }
            $layeredHandler->addLayer($layer);
        }
        return $layeredHandler;
    }

    /**
     * @param string   $hostname
     * @param int      $port
     * @param string   $tag
     * @param int|null $minLevel
     * @return CM_Log_Handler_Fluentd
     * @throws CM_Exception_Invalid
     */
    public function createFluentdLogger($hostname, $port, $tag, $minLevel = null) {
        $fluentd = new \Fluent\Logger\FluentLogger($hostname, $port);
        $appName = CM_App::getInstance()->getName();
        $contextFormatter = new CM_Log_ContextFormatter_Cargomedia($appName);
        return new CM_Log_Handler_Fluentd($fluentd, $contextFormatter, $tag, $minLevel);
    }

    /**
     * @param CM_OutputStream_Interface $stream
     * @param CM_Log_Formatter_Abstract $formatter
     * @param int|null                  $minLevel
     * @return CM_Log_Handler_Stream
     */
    protected function _createStreamHandler(CM_OutputStream_Interface $stream, CM_Log_Formatter_Abstract $formatter, $minLevel = null) {
        $minLevel = null !== $minLevel ? (int) $minLevel : $minLevel;

        return new CM_Log_Handler_Stream($stream, $formatter, $minLevel);
    }
}
