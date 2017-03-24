<?php

class CM_Debug {

    /** @var array */
    private $_stats = array();

    /** @var bool */
    private $_enabled;

    /**
     * @param bool $enabled
     */
    public function __construct($enabled) {
        $this->_enabled = (bool) $enabled;
    }

    /**
     * @return array[]
     */

    public function getStats() {
        return $this->_stats;
    }

    /**
     * @param string          $key
     * @param string|string[] $value
     */
    public function incStats($key, $value) {
        if (!$this->_enabled) {
            return;
        }
        if (!array_key_exists($key, $this->_stats)) {
            $this->_stats[$key] = array();
        }
        $this->_stats[$key][] = $value;
    }

    /**
     * @return bool
     */
    public function isEnabled() {
        return $this->_enabled;
    }

    /**
     * @param bool $state
     */
    public function setEnabled($state) {
        $this->_enabled = $state;
    }

    /**
     * @deprecated use CM_Service_Manager::getInstance()->getDebug()
     * @return CM_Debug
     */
    public static function getInstance() {
        return CM_Service_Manager::getInstance()->getDebug();
    }

    /**
     * @param string     $message
     * @param array|null $extra
     */
    public static function log($message, array $extra = null) {
        $message = (string) $message;
        $trace = debug_backtrace();
        $className = 'none';
        $functionName = 'none';
        if (isset($trace[1])) {
            $className = isset($trace[1]['class']) ? $trace[1]['class'] : $className;
            $functionName = isset($trace[1]['function']) ? $trace[1]['function'] : $functionName;
        }
        $context = new CM_Log_Context();
        if (null !== $extra) {
            $context->setExtra($extra);
        }
        CM_Service_Manager::getInstance()->getLogger()->debug(sprintf('%s:%s - %s', $className, $functionName, $message), $context);
    }
}
