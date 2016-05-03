<?php

interface CM_Log_Handler_HandlerInterface {

    /**
     * @param CM_Log_Record $record
     */
    public function handleRecord(CM_Log_Record $record);

    /**
     * @param CM_Log_Record $record
     * @return bool
     */
    public function isHandling(CM_Log_Record $record);
}
