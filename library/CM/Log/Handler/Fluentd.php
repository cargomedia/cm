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
        $formattedRecord = $this->_sanitizeRecord($formattedRecord);
        $this->_getFluentd()->post($this->_tag, $formattedRecord);
    }

    /**
     * @param CM_Log_Record $record
     * @return array
     */
    protected function _formatRecord(CM_Log_Record $record) {
        return $this->_contextFormatter->formatRecordContext($record);
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
     * @param array $data
     * @return array
     */
    protected function _encodeAsArray(array $data) {
        $iterator = new RecursiveIteratorIterator(new RecursiveArrayIterator($data));
        $result = [];
        foreach ($iterator as $key => $value) {
            $result[] = [
                'key'   => $this->_getKeysPath($iterator),
                'value' => $value,
            ];
        }
        usort($result, function (array $a, array $b) {
            return strcmp($a['key'], $b['key']);
        });
        return $result;
    }

    /**
     * @param RecursiveIteratorIterator $iterator
     * @return string
     */
    private function _getKeysPath(RecursiveIteratorIterator $iterator) {
        $i = 0;
        $keyList = [];
        while ($subIterator = $iterator->getSubIterator($i)) {
            $keyList[] = $subIterator->key();
            $i += 1;
        }
        return join('.', $keyList);
    }
}
