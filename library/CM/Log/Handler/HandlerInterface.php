<?php

class CM_Log_Handler_HandlerInterface {

    /** @var bool */
    private $_bubble;

    /**
     * @return bool
     */
    public function getBubble() {
    }

    /**
     * @param CM_Log_Record $record
     * @return bool Whether the record was successfully handled
     */
    public function handleRecord(CM_Log_Record $record) {
    }

}
