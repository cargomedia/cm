<?php

class CM_Log_Logger {

    const DEBUG = 100;
    const INFO = 200;
    const WARNING = 300;
    const ERROR = 400;
    const CRITICAL = 500;

    /**
     * @var array $levels Logging levels
     */
    protected static $_levels = array(
        self::DEBUG    => 'DEBUG',
        self::INFO     => 'INFO',
        self::WARNING  => 'WARNING',
        self::ERROR    => 'ERROR',
        self::CRITICAL => 'CRITICAL',
    );

    /** @var array */
    private $_handlersLayerList;

    /** @var CM_Log_Context */
    private $_contextGlobal;

    /**
     * @param CM_Log_Context $contextGlobal
     * @param array          $handlersLayerList
     * @throws CM_Exception_Invalid
     */
    public function __construct(CM_Log_Context $contextGlobal, array $handlersLayerList) {
        if (empty($handlersLayerList)) {
            throw new CM_Exception_Invalid('Logger should have at least 1 handler layer');
        }
        $this->_contextGlobal = $contextGlobal;
        foreach ($handlersLayerList as $handlersLayer) {
            $this->_addHandlersLayer($handlersLayer);
        }
    }

    /**
     * @param string              $message
     * @param int                 $level
     * @param CM_Log_Context|null $context
     * @return CM_Log_Logger
     */
    public function addMessage($message, $level, CM_Log_Context $context = null) {
        $message = (string) $message;
        $level = (int) $level;
        $context = $this->_mergeWithGlobalContext($context);
        return $this->_addRecord(new CM_Log_Record($level, $message, $context));
    }

    /**
     * @param Exception           $exception
     * @param int|null            $logLevel
     * @param CM_Log_Context|null $context
     * @return CM_Log_Logger
     */
    public function addException(Exception $exception, $logLevel = null, CM_Log_Context $context = null) {
        $context = $this->_mergeWithGlobalContext($context);
        return $this->_addRecord(new CM_Log_Record_Exception($exception, $context, $logLevel));
    }

    /**
     * @param string              $message
     * @param CM_Log_Context|null $context
     * @return CM_Log_Logger
     */
    public function debug($message, CM_Log_Context $context = null) {
        return $this->addMessage($message, self::DEBUG, $context);
    }

    /**
     * @param string              $message
     * @param CM_Log_Context|null $context
     * @return CM_Log_Logger
     */
    public function info($message, CM_Log_Context $context = null) {
        return $this->addMessage($message, self::INFO, $context);
    }

    /**
     * @param string              $message
     * @param CM_Log_Context|null $context
     * @return CM_Log_Logger
     */
    public function warning($message, CM_Log_Context $context = null) {
        return $this->addMessage($message, self::WARNING, $context);
    }

    /**
     * @param string              $message
     * @param CM_Log_Context|null $context
     * @return CM_Log_Logger
     */
    public function error($message, CM_Log_Context $context = null) {
        return $this->addMessage($message, self::ERROR, $context);
    }

    /**
     * @param string              $message
     * @param CM_Log_Context|null $context
     * @return CM_Log_Logger
     */
    public function critical($message, CM_Log_Context $context = null) {
        return $this->addMessage($message, self::CRITICAL, $context);
    }

    /**
     * @param array $handlersLayer
     * @throws CM_Exception_Invalid
     */
    protected function _addHandlersLayer(array $handlersLayer) {
        $currentLayerIdx = sizeof($this->_handlersLayerList);
        if (empty($handlersLayer)) {
            throw new CM_Exception_Invalid('Empty handlers layer');
        }
        foreach ($handlersLayer as $handler) {
            if (!$handler instanceof CM_Log_Handler_HandlerInterface) {
                throw new CM_Exception_Invalid('Not logger handler instance');
            }
            $this->_handlersLayerList[$currentLayerIdx][] = $handler;
        }
    }

    /**
     * @param CM_Log_Record $record
     * @return CM_Log_Logger
     */
    protected function _addRecord(CM_Log_Record $record) {
        $this->_addRecordToLayer($record, 0);
        return $this;
    }

    /**
     * @param CM_Log_Record $record
     * @param int           $layerIdx
     * @throws CM_Exception_Invalid
     */
    protected function _addRecordToLayer(CM_Log_Record $record, $layerIdx) {
        $layerIdx = (int) $layerIdx;

        if (!array_key_exists($layerIdx, $this->_handlersLayerList)) {
            throw new CM_Exception_Invalid('Wrong offset for handlers layer');
        }
        $handlerList = $this->_handlersLayerList[$layerIdx];
        $exceptionList = [];

        $numberOfHandlers = sizeof($handlerList);
        for ($i = 0, $n = $numberOfHandlers; $i < $n; $i++) {
            /** @var CM_Log_Handler_HandlerInterface $handler */
            $handler = $handlerList[$i];
            try {
                $handler->handleRecord($record);
            } catch (Exception $e) {
                $exceptionList[] = new CM_Log_HandlingException($e);
            }
        }

        if (!empty($exceptionList)) {
            if (sizeof($exceptionList) === $numberOfHandlers) { //all handlers failed so use next layer
                $nextLayerIdx = $layerIdx + 1;
                if (array_key_exists($nextLayerIdx, $this->_handlersLayerList)) {
                    $this->_addRecordToLayer($record, $nextLayerIdx);
                }
            }
            $this->_logHandlersExceptions($record, $exceptionList, $record->getContext());
        }
    }

    /**
     * @param CM_Log_Record  $record
     * @param Exception[]    $exceptionList
     * @param CM_Log_Context $context
     * @throws CM_Exception_Invalid
     */
    protected function _logHandlersExceptions(CM_Log_Record $record, array $exceptionList, CM_Log_Context $context) {
        if ($record instanceof CM_Log_Record_Exception && $record->getException() instanceof CM_Log_HandlingException) {
            return;
        }
        foreach ($exceptionList as $exception) {
            $this->_addRecordToLayer(new CM_Log_Record_Exception($exception, $context), 0);
        }
    }

    /**
     * @param CM_Log_Context|null $context
     * @return CM_Log_Context
     */
    protected function _mergeWithGlobalContext(CM_Log_Context $context = null) {
        if (null === $context) {
            $context = new CM_Log_Context();
        }
        return $this->_contextGlobal->merge($context);
    }

    /**
     * Gets all supported logging levels.
     *
     * @return array Assoc array with human-readable level names => level codes.
     */
    public static function getLevels() {
        return array_flip(self::$_levels);
    }

    /**
     * Gets the name of the logging level.
     *
     * @param  int $level
     * @return string
     * @throws CM_Exception_Invalid
     */
    public static function getLevelName($level) {
        $level = (int) $level;
        if (!isset(self::$_levels[$level])) {
            throw new CM_Exception_Invalid('Level `' . $level . '` is not defined, use one of: ' . implode(', ', array_keys(self::$_levels)));
        }
        return self::$_levels[$level];
    }

    /**
     * @param int $level
     * @return bool
     */
    public static function hasLevel($level) {
        $level = (int) $level;
        return isset(self::$_levels[$level]);
    }
}
