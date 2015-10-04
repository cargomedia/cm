<?php

interface CM_Log_Formatter_Interface {

    /**
     * @param CM_Log_Record $record
     * @return string
     */
    public function renderMessage(CM_Log_Record $record);

    /**
     * @param CM_Log_Record $record
     * @return string|null
     */
    public function renderContext(CM_Log_Record $record);

    /**
     * @param CM_Log_Record_Exception $record
     * @return string
     */
    public function renderException(CM_Log_Record_Exception $record);

}
