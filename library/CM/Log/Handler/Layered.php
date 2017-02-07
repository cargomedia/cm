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
            throw new CM_Exception_Invalid('Layer not found', null, ['layerNumber' => $number]);
        }
        return $this->_layers[$number];
    }

    /**
     * @param CM_Log_Record                          $record
     * @param int                                    $layerIdx
     * @param CM_Log_Handler_HandlerInterface[]|null $excludedHandlers
     * @throws CM_Exception_Invalid
     */
    protected function _addRecordToLayer(CM_Log_Record $record, $layerIdx, array $excludedHandlers = null) {
        $layer = $this->_getLayer($layerIdx);

        $handlersRecorded = 0;
        $failingHandlers = [];
        $exceptionList = [];
        foreach ($layer->getHandlers() as $handler) {
            if (!in_array($handler, (array) $excludedHandlers)) {
                try {
                    $handler->handleRecord($record);
                    if ($handler->isHandling($record)) {
                        $handlersRecorded += 1;
                    }
                } catch (Exception $e) {
                    $failingHandlers[] = $handler;
                    $exceptionList[] = $e;
                }
            }
        }
        if (!empty($exceptionList)) {
            if (0 === $handlersRecorded) { //all handlers failed or didn't handle so use next layer
                $nextLayerIdx = $layerIdx + 1;
                if ($this->_hasLayer($nextLayerIdx)) {
                    $this->_addRecordToLayer($record, $nextLayerIdx);
                    $this->_addExceptionListToLayer($exceptionList, $record->getContext(), $nextLayerIdx, $failingHandlers);
                }
            } else {
                $this->_addExceptionListToLayer($exceptionList, $record->getContext(), $layerIdx, $failingHandlers);
            }
        }
    }

    /**
     * @param Exception[]                            $exceptionList
     * @param CM_Log_Context                         $context
     * @param int                                    $layerIdx
     * @param CM_Log_Handler_HandlerInterface[]|null $excludeHandlers
     * @internal param CM_Log_Record $record
     */
    protected function _addExceptionListToLayer(array $exceptionList, CM_Log_Context $context, $layerIdx, array $excludeHandlers = null) {
        foreach ($exceptionList as $exception) {
            $newContext = clone $context;
            $newContext->setException($exception);
            $newRecord = new CM_Log_Record(CM_Log_Logger::ERROR, $exception->getMessage(), $newContext);
            $this->_addRecordToLayer($newRecord, $layerIdx, $excludeHandlers);
        }
    }
}
