<?php

interface CM_Log_ContextFormatter_Interface {

    /**
     * @param CM_Log_Record $record
     * @return array
     */
    public function getRecordContext(CM_Log_Record $record);

    /**
     * @param CM_Log_Context $context
     * @return array
     */
    public function getContext(CM_Log_Context $context);

    /**
     * @param CM_Log_Context $context
     * @return array
     */
    public function getAppContext(CM_Log_Context $context);
}
