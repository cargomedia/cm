<?php

class CM_Log_Logger {

    const DEBUG = 100;
    const INFO = 200;
    const WARNING = 300;
    const ERROR = 400;
    const CRITICAL = 500;
    const NOTSET = 999;

    /**
     * Logging levels from syslog protocol defined in RFC 5424
     *
     * @var array $levels Logging levels
     */
    protected static $levels = array(
        self::DEBUG    => 'DEBUG',
        self::INFO     => 'INFO',
        self::WARNING  => 'WARNING',
        self::ERROR    => 'ERROR',
        self::CRITICAL => 'CRITICAL',
        self::NOTSET   => 'NOTSET',
    );

    /** @var CM_Log_Handler_HandlerInterface[] */
    private $_handlerList;

    /** @var CM_Log_Handler_HandlerInterface[] */
    private $_fallbackList;

    /** @var CM_Log_Context  */
    private $_globalContext;

    /**
     * @param array|null          $handlerList
     * @param array|null          $fallbackList
     * @param CM_Log_Context|null $globalContext
     */
    public function __construct(array $handlerList = null, array $fallbackList = null, CM_Log_Context $globalContext = null) {
        $this->_handlerList = [];
        $this->_fallbackList = [];
        $this->_globalContext = $globalContext ?: new CM_Log_Context();
        $this->addHandlers($handlerList ?: []);
        $this->addFallbacks($fallbackList ?: []);
    }

    /**
     * @param CM_Log_Record $record
     */
    public function addRecord(CM_Log_Record $record) {
        foreach ($this->getHandlers() as $handler) {
            if ($handler->handleRecord($record) && !$handler->getBubble()) {
                break;
            }
        }
    }

    /**
     * @param                     $message
     * @param int                 $level
     * @param CM_Log_Context|null $context
     */
    public function addMessage($message, $level = null, CM_Log_Context $context = null) {
        if(null === $level) {
            $level = self::NOTSET;
        }
        $context = $this->_mergeToGlobalContext($context);
        $this->addRecord(new CM_Log_Record($level, $message, $context));
    }

    /**
     * @param Exception           $exception
     * @param CM_Log_Context|null $context
     */
    public function addException(Exception $exception, CM_Log_Context $context = null) {
        $context = $this->_mergeToGlobalContext($context);
        $this->addRecord(new CM_Log_Record_Exception($exception, $context));
    }

    /**
     * @param array $handlerList
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
        /** @var CM_Log_Handler_HandlerInterface $handler */
        foreach ($this->_handlerList as $currentHandler) {
            $currentHandler->setBubble(true);
        }
        $handler->setBubble(false);
        $this->_handlerList[] = $handler;
    }

    /**
     * @param array $handlerList
     */
    public function addFallbacks(array $handlerList) {
        foreach ($handlerList as $handler) {
            $this->addFallback($handler);
        }
    }

    /**
     * @param CM_Log_Handler_HandlerInterface $handler
     */
    public function addFallback(CM_Log_Handler_HandlerInterface $handler) {
        $handler->setBubble(false);
        $this->_handlerList[] = $handler;
    }

    /**
     * @return CM_Log_Handler_HandlerInterface[]
     */
    public function getHandlers() {
        return array_merge($this->_handlerList, $this->_fallbackList);
    }

    /**
     * @param                     $message
     * @param CM_Log_Context|null $context
     */
    public function debug($message, CM_Log_Context $context = null) {
        $this->addMessage($message, self::DEBUG, $context);
    }

    /**
     * @param                     $message
     * @param CM_Log_Context|null $context
     */
    public function info($message, CM_Log_Context $context = null) {
        $this->addMessage($message, self::INFO, $context);
    }

    /**
     * @param                     $message
     * @param CM_Log_Context|null $context
     */
    public function warning($message, CM_Log_Context $context = null) {
        $this->addMessage($message, self::WARNING, $context);
    }

    /**
     * @param                     $message
     * @param CM_Log_Context|null $context
     */
    public function error($message, CM_Log_Context $context = null) {
        $this->addMessage($message, self::ERROR, $context);
    }

    /**
     * @param                     $message
     * @param CM_Log_Context|null $context
     */
    public function critical($message, CM_Log_Context $context = null) {
        $this->addMessage($message, self::CRITICAL, $context);
    }

    /**
     * @param CM_Log_Context|null $context
     * @return CM_Log_Context
     */
    protected function _mergeToGlobalContext(CM_Log_Context $context = null) {
        $mergedContext = clone($context ?: new CM_Log_Context());
        $mergedContext->merge($this->_globalContext);
        return $mergedContext;
    }

    /**
     * Gets all supported logging levels.
     *
     * @return array Assoc array with human-readable level names => level codes.
     */
    public static function getLevels() {
        return array_flip(static::$levels);
    }

    /**
     * Gets the name of the logging level.
     *
     * @param  integer $level
     * @return string
     * @throws CM_Exception_Invalid
     */
    public static function getLevelName($level) {
        if (!isset(static::$levels[$level])) {
            throw new CM_Exception_Invalid('Level "' . $level . '" is not defined, use one of: ' . implode(', ', array_keys(static::$levels)));
        }
        return static::$levels[$level];
    }

    /**
     * Gets the logging level associated with a name.
     *
     * @param  string $name
     * @return int
     * @throws CM_Exception_Invalid
     */
    public static function getLevelCode($name) {
        $levels = self::getLevels();
        $name = strtoupper($name);
        if (!isset($levels[$name])) {
            throw new CM_Exception_Invalid('Level "' . $name . '" is not defined, use one of: ' . implode(', ', array_keys($levels)));
        }
        return $levels[$name];
    }
}
