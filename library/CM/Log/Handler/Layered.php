<?php

class CM_Log_Handler_Layered implements CM_Log_Handler_HandlerInterface {

    /** @var CM_Log_Handler_Layered_Layer[] */
    private $_layers;

    /**
     * @param CM_Log_Handler_Layered_Layer[]|null $layers
     */
    public function __construct(array $layers = null) {
        $this->setLayers((array) $layers);
    }

    /**
     * @param CM_Log_Handler_Layered_Layer[] $handlers
     */
    public function setLayers($handlers) {
        $this->_layers = [];
        foreach ($handlers as $layer) {
            /** CM_Log_Handler_Layered_Layer $layer */
            $this->addLayer($layer);
        }
    }

    /**
     * @param CM_Log_Handler_Layered_Layer $layer
     */
    public function addLayer(CM_Log_Handler_Layered_Layer $layer) {
        $this->_layers[] = $layer;
    }

    public function handleRecord(CM_Log_Record $record) {
        $this->_addRecordToLayer($record, 0);
    }

    public function isHandling(CM_Log_Record $record) {
        return true;
    }

    /**
     * @param $number
     * @return bool
     */
    protected function _hasLayer($number) {
        return array_key_exists($number, $this->_layers);
    }

    /**
     * @param int $number
     * @return CM_Log_Handler_Layered_Layer
     * @throws CM_Exception_Invalid
     */
    protected function _getLayer($number) {
        if (!$this->_hasLayer($number)) {
            throw new CM_Exception_Invalid("Layer `#{$number}` not found");
        }
        return $this->_layers[$number];
    }

    /**
     * @param CM_Log_Record $record
     * @param int           $layerIdx
     * @throws CM_Exception_Invalid
     */
    protected function _addRecordToLayer(CM_Log_Record $record, $layerIdx) {
        $layer = $this->_getLayer($layerIdx);

        $exceptionList = [];
        $handlersRecorded = 0;
        foreach ($layer->getHandlers() as $handler) {
            try {
                $handler->handleRecord($record);
                if ($handler->isHandling($record)) {
                    $handlersRecorded += 1;
                }
            } catch (Exception $e) {
                $handlerClassName = get_class($handler);
                $exceptionList[] = new CM_Log_HandlingException("Logger handler `{$handlerClassName}` failed", $e);
            }
        }

        if (!empty($exceptionList)) {
            if (0 === $handlersRecorded) { //all handlers failed or didn't handle so use next layer
                $nextLayerIdx = $layerIdx + 1;
                if ($this->_hasLayer($nextLayerIdx)) {
                    $this->_addRecordToLayer($record, $nextLayerIdx);
                }
            }
            $this->_logHandlersExceptions($record, $exceptionList);
        }
    }

    /**
     * @param CM_Log_Record              $record
     * @param CM_Log_HandlingException[] $exceptionList
     * @throws CM_Exception_Invalid
     */
    protected function _logHandlersExceptions(CM_Log_Record $record, array $exceptionList) {
        $context = $record->getContext();
        $appContext = $context->getAppContext();
        if ($appContext->hasException() && $appContext->getException() instanceof CM_Log_HandlingException) {
            return;
        }
        \Functional\each($exceptionList, function (CM_Log_HandlingException $exception) use ($context) {
            $newContext = clone $context;
            $newContext->getAppContext()->setException($exception->getOriginalException());
            $newRecord = new CM_Log_Record(CM_Log_Logger::ERROR, $exception->getMessage(), $newContext);
            $this->handleRecord($newRecord);
        });
    }
}
