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
        100 => 'DEBUG',
        200 => 'INFO',
        300 => 'WARNING',
        400 => 'ERROR',
        500 => 'CRITICAL',
        999 => 'NOTSET',
    );

    /** @var CM_Log_Handler_HandlerInterface[] */
    private $_handlerList = [];

    /** @var CM_Log_Handler_HandlerInterface[] */
    private $_fallbackList = [];

    /**
     * @param array|null $handlerList
     * @param array|null $fallbackList
     */
    public function __construct(array $handlerList = null, array $fallbackList = null) {
        $handlerList = $handlerList ?: [];
        $fallbackList = $fallbackList ?: [];
        $this->addHandlers($handlerList);
        $this->addFallbacks($fallbackList);
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
     * @param string     $message
     * @param int        $level
     * @param array|null $options
     */
    public function addMessage($message, $level = self::NOTSET, array $options = null) {
        $this->addRecord(new CM_Log_Record($level, $message, new CM_Log_Context($options)));
    }

    /**
     * @param Exception  $exception
     * @param array|null $options
     */
    public function addException(Exception $exception, array $options = null) {
        $this->addRecord(new CM_Log_Record_Exception($exception, new CM_Log_Context($options)));
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
     * @param string     $message
     * @param array|null $options
     */
    public function debug($message, $options = null) {
        $this->addMessage($message, self::DEBUG, $options);
    }

    /**
     * @param string     $message
     * @param array|null $options
     */
    public function info($message, $options = null) {
        $this->addMessage($message, self::INFO, $options);
    }

    /**
     * @param string     $message
     * @param array|null $options
     */
    public function warning($message, $options = null) {
        $this->addMessage($message, self::WARNING, $options);
    }

    /**
     * @param string     $message
     * @param array|null $options
     */
    public function error($message, $options = null) {
        $this->addMessage($message, self::ERROR, $options);
    }

    /**
     * @param string     $message
     * @param array|null $options
     */
    public function critical($message, $options = null) {
        $this->addMessage($message, self::CRITICAL, $options);
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
