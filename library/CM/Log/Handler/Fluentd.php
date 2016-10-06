<?php

use Fluent\Logger\FluentLogger;

class CM_Log_Handler_Fluentd extends CM_Log_Handler_Abstract {

    /** @var \Fluent\Logger\FluentLogger */
    protected $_fluentdLogger;

    /** @var CM_Log_ContextFormatter_Interface */
    protected $_contextFormatter;

    /** @var CM_Log_Encoder_Fluentd */
    protected $_encoder;

    /** @var string */
    protected $_tag;

    /**
     * @param FluentLogger                      $fluentdLogger
     * @param CM_Log_ContextFormatter_Interface $contextFormatter
     * @param CM_Log_Encoder_Fluentd            $encoder
     * @param string                            $tag
     * @param int|null                          $minLevel
     */
    public function __construct(FluentLogger $fluentdLogger, CM_Log_ContextFormatter_Interface $contextFormatter, CM_Log_Encoder_Fluentd $encoder, $tag, $minLevel = null) {
        parent::__construct($minLevel);
        $this->_fluentdLogger = $fluentdLogger;
        $this->_contextFormatter = $contextFormatter;
        $this->_encoder = $encoder;
        $this->_tag = (string) $tag;
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
        $sanitizedRecord = $this->_sanitizeRecord($formattedRecord);
        $encodedRecord = $this->_encodeRecord($sanitizedRecord);
        $this->_getFluentd()->post($this->_tag, $encodedRecord);
    }

    /**
     * @param CM_Log_Record $record
     * @return array
     */
    protected function _formatRecord(CM_Log_Record $record) {
        $levelsMapping = array_flip(CM_Log_Logger::getLevels());
        $context = $record->getContext();

        $result = [
            'message'   => (string) $record->getMessage(),
            'level'     => strtolower($levelsMapping[$record->getLevel()]),
            'timestamp' => $record->getCreatedAt()->format(DateTime::ISO8601),
        ];
        $result = array_merge($result, $this->_contextFormatter->formatContext($context));
        return $result;
    }

    /**
     * @param array $formattedRecord
     * @return array
     */
    protected function _sanitizeRecord(array $formattedRecord) {
        array_walk_recursive($formattedRecord, function (&$value, $key) {
            if (is_string($value) && !mb_check_encoding($value, 'UTF-8')) {
                $value = CM_Util::sanitizeUtf($value);
            }
        });
        return $formattedRecord;
    }

    /**
     * @param array $entry
     * @return array
     */
    protected function _encodeRecord(array $entry) {
        return $this->_encoder->encode($entry);
    }
}
