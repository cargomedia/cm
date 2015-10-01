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
    protected static $levels = array(
        self::DEBUG    => 'DEBUG',
        self::INFO     => 'INFO',
        self::WARNING  => 'WARNING',
        self::ERROR    => 'ERROR',
        self::CRITICAL => 'CRITICAL',
    );

    /** @var CM_Log_Handler_HandlerInterface[] */
    private $_handlerList;

    /** @var CM_Log_Handler_HandlerInterface[] */
    private $_fallbackList;

    /** @var CM_Log_Context */
    private $_globalContext;

    /**
     * @param array|null          $handlerList
     * @param array|null          $fallbackList
     * @param CM_Log_Context|null $globalContext
     */
    public function __construct(array $handlerList = null, array $fallbackList = null, CM_Log_Context $globalContext = null) {
        if (null === $handlerList) {
            $handlerList = [];
        }
        if (null === $fallbackList) {
            $fallbackList = [];
        }
        if (null === $globalContext) {
            $globalContext = new CM_Log_Context();
        }

        $this->_globalContext = $globalContext;
        $this->_handlerList = [];
        $this->_fallbackList = [];

        $this->addHandlers($handlerList);
        $this->addFallbacks($fallbackList);
    }

    /**
     * @param CM_Log_Record $record
     */
    public function addRecord(CM_Log_Record $record) {
        $handlerHasFailed = false;
        foreach ($this->_handlerList as $handler) {
            if (!$handler->handleRecord($record)) {
                $handlerHasFailed = true;
            }
        }
        if (empty($this->_handlerList) || $handlerHasFailed) {
            foreach ($this->_fallbackList as $handler) {
                if ($handler->handleRecord($record)) {
                    break;
                }
            }
        }
    }

    /**
     * @param string $message
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
        $this->_fallbackList[] = $handler;
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
    protected function _mergeWithGlobalContext(CM_Log_Context $context = null) {
        if (null === $context) {
            $context = new CM_Log_Context();
        }
        return $this->_globalContext->merge($context);
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
     * @param  int $level
     * @return string
     * @throws CM_Exception_Invalid
     */
    public static function getLevelName($level) {
        $level = (int) $level;
        if (!isset(static::$levels[$level])) {
            throw new CM_Exception_Invalid('Level `' . $level . '` is not defined, use one of: ' . implode(', ', array_keys(static::$levels)));
        }
        return static::$levels[$level];
    }

    /**
     * @param int $level
     * @return bool
     */
    public static function hasLevel($level) {
        $level = (int) $level;
        return isset(static::$levels[(int) $level]);
    }
}
