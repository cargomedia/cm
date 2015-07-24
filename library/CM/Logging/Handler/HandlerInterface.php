<?php

class CM_Logging_Handler_HandlerInterface {

    /** @var bool */
    private $_bubble;

    /**
     * @return bool
     */
    public function getBubble() {
    }

    /**
     * @param CM_Logging_Record $record
     * @return bool Whether the record was successfully handled
     */
    public function handleRecord(CM_Logging_Record $record) {
    }

}
