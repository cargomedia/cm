<?php

use Fluent\Logger\FluentLogger;

class CM_Log_Handler_Fluentd extends CM_Log_Handler_Abstract {

    /** @var \Fluent\Logger\FluentLogger */
    protected $_fluentdLogger;

    /** @var CM_Log_ContextFormatter_Interface */
    protected $_contextFormatter;

    /** @var string */
    protected $_tag;

    /**
     * @param FluentLogger                      $fluentdLogger
     * @param CM_Log_ContextFormatter_Interface $contextFormatter
     * @param string                            $tag
     * @param int|null                          $minLevel
     */
    public function __construct(FluentLogger $fluentdLogger, CM_Log_ContextFormatter_Interface $contextFormatter, $tag, $minLevel = null) {
        parent::__construct($minLevel);
        $this->_fluentdLogger = $fluentdLogger;
        $this->_contextFormatter = $contextFormatter;
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
            'timestamp' => $record->getCreatedAt()->format('Y-m-d\TH:i:s.uO'), // ISO8601 with fractions
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
     * @param array $value
     * @return array
     */
    protected function _encodeRecord($value) {
        if ($value instanceof DateTime) {
            return $value->format('c');
        }

        if (is_object($value)) {
            $encoded = '[';
            $encoded .= get_class($value);
            if ($value instanceof CM_Model_Abstract) {
                $encoded .= ':' . $value->getId();
            }
            $encoded .= ']';
            return $encoded;
        }

        if (is_array($value)) {
            return array_map([$this, '_encodeRecord'], $value);
        }

        return $value;
    }
}
