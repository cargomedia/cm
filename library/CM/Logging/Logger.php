<?php

class CM_Logging_Logger {

    const DEBUG = 100;
    const INFO = 200;
    const NOTICE = 250;
    const WARNING = 300;
    const CRITICAL = 500;
    const ALERT = 550;
    const EMERGENCY = 600;

    /** @var CM_Logging_Handler_HandlerInterface[] */
    private $_handlerList = [];

    /**
     * @param CM_Logging_Record $record
     */
    public function addRecord(CM_Logging_Record $record) {
    }

    /**
     * @param int        $level
     * @param Exception  $exception
     * @param array|null $options
     */
    public function addException($level, Exception $exception, array $options = null) {
    }

    /**
     * @param int        $level
     * @param string     $message
     * @param array|null $options
     */
    public function addMessage($level, $message, array $options = null) {
    }
}
