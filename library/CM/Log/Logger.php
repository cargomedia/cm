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

    /** @var CM_Log_Handler_HandlerInterface[] */
    private $_handlerList;

    /** @var CM_Log_Context */
    private $_contextGlobal;

    /**
     * @param CM_Log_Context                         $contextGlobal
     * @param CM_Log_Handler_HandlerInterface[]|null $handlerList
     */
    public function __construct(CM_Log_Context $contextGlobal, array $handlerList = null) {
        if (null === $handlerList) {
            $handlerList = [];
        }
        $this->_handlerList = [];
        $this->_contextGlobal = $contextGlobal;

        $this->addHandlers($handlerList);
    }

    /**
     * @param CM_Log_Record $record
     * @throws CM_Log_HandlingException
     */
    public function addRecord(CM_Log_Record $record) {
        $handlerExceptionList = [];
        $handlerNameList = [];
        foreach ($this->_handlerList as $handler) {
            try {
                $handler->handleRecord($record);
            } catch (Exception $e) {
                $handlerExceptionList[] = $e;
            }
        }
        if (!empty($handlerExceptionList)) {
            $exceptionMessage = sizeof($handlerExceptionList) . ' handler(s) failed to process a record.';
            throw new CM_Log_HandlingException($exceptionMessage, $handlerExceptionList);
        }
    }

    /**
     * @param string              $message
     * @param int                 $level
     * @param CM_Log_Context|null $context
     */
    public function addMessage($message, $level, CM_Log_Context $context = null) {
        $message = (string) $message;
        $level = (int) $level;
        $context = $this->_mergeWithGlobalContext($context);
        $this->addRecord(new CM_Log_Record($level, $message, $context));
    }

    /**
     * @param Exception           $exception
     * @param CM_Log_Context|null $context
     */
    public function addException(Exception $exception, CM_Log_Context $context = null) {
        $context = $this->_mergeWithGlobalContext($context);
        $this->addRecord(new CM_Log_Record_Exception($exception, $context));
    }

    /**
     * @param CM_Log_Handler_HandlerInterface[] $handlerList
     */
    public function addHandlers(array $handlerList) {
        foreach ($handlerList as $handler) {
            $this->addHandler($handler);
        }
    }

    /**
     * @param CM_Log_Handler_HandlerInterface $handler
     */
    public function addHandler(CM_Log_Handler_HandlerInterface $handler) {
        $this->_handlerList[] = $handler;
    }

    /**
     * @param string              $message
     * @param CM_Log_Context|null $context
     */
    public function debug($message, CM_Log_Context $context = null) {
        $this->addMessage($message, self::DEBUG, $context);
    }

    /**
     * @param string              $message
     * @param CM_Log_Context|null $context
     */
    public function info($message, CM_Log_Context $context = null) {
        $this->addMessage($message, self::INFO, $context);
    }

    /**
     * @param string              $message
     * @param CM_Log_Context|null $context
     */
    public function warning($message, CM_Log_Context $context = null) {
        $this->addMessage($message, self::WARNING, $context);
    }

    /**
     * @param string              $message
     * @param CM_Log_Context|null $context
     */
    public function error($message, CM_Log_Context $context = null) {
        $this->addMessage($message, self::ERROR, $context);
    }

    /**
     * @param string              $message
     * @param CM_Log_Context|null $context
     */
    public function critical($message, CM_Log_Context $context = null) {
        $this->addMessage($message, self::CRITICAL, $context);
    }

    /**
     * @param CM_Log_Context|null $context
     * @return CM_Log_Context
     */
    protected function _mergeWithGlobalContext(CM_Log_Context $context = null) {
        if (null === $context) {
            $mergedContext = clone($this->_contextGlobal);
        } else {
            $mergedContext = $this->_contextGlobal->merge($context);
        }
        return $mergedContext;
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
