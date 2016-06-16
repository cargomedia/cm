<?php

use Fluent\Logger\FluentLogger;

class CM_Log_Handler_Fluentd extends CM_Log_Handler_Abstract {

    /** @var \Fluent\Logger\FluentLogger */
    protected $_fluentdLogger;

    /** @var string */
    protected $_tag;

    /** @var string */
    protected $_appName;

    /**
     * @param FluentLogger $fluentdLogger
     * @param string       $tag
     * @param string       $appName
     * @param int|null     $minLevel
     */
    public function __construct(FluentLogger $fluentdLogger, $tag, $appName, $minLevel = null) {
        parent::__construct($minLevel);
        $this->_fluentdLogger = $fluentdLogger;
        $this->_tag = (string) $tag;
        $this->_appName = (string) $appName;
    }

    /**
     * @return \Fluent\Logger\FluentLogger
     */
    protected function _getFluentd() {
        return $this->_fluentdLogger;
    }

    /**
     * @param CM_Log_Record $record
     */
    protected function _writeRecord(CM_Log_Record $record) {
        $formattedRecord = $this->_formatRecord($record);
        $this->_getFluentd()->post($this->_tag, $formattedRecord);
    }

    /**
     * @param CM_Log_Record $record
     * @return array
     */
    protected function _formatRecord(CM_Log_Record $record) {
        $contextFormatter = new CM_Log_ContextFormatter_Cargomedia($this->_appName);
        return $contextFormatter->getRecordContext($record);
    }
}
