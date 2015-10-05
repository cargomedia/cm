<?php

interface CM_Log_Formatter_Interface {

    /**
     * @param CM_Log_Record $record
     * @return string
     */
    public function render(CM_Log_Record $record);
}
