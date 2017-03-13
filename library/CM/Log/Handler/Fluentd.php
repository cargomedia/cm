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
     * @throws CM_Exception_Invalid
     */
    protected function _writeRecord(CM_Log_Record $record) {
        $formattedRecord = $this->_formatRecord($record);
        $sanitizedRecord = $this->_sanitizeRecord($formattedRecord);
        $encodedRecord = $this->_encodeRecord($sanitizedRecord);

        $errorMessage = null;
        $this->_getFluentd()->registerErrorHandler(function (FluentLogger $fluent, \Fluent\Logger\Entity $entity, $error) use (&$errorMessage) {
            $errorMessage = $error;
        });
        $res = $this->_getFluentd()->post($this->_tag, $encodedRecord);
        $this->_getFluentd()->unregisterErrorHandler();
        if (false === $res) {
            $metaInfo = $errorMessage ? ['originalExceptionMessage' => (string) $errorMessage] : null;
            throw new CM_Exception_Invalid('Could not write to fluentd', null, $metaInfo);
        }
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
     * @param array      $value
     * @param mixed|null $key
     * @return array
     */
    protected function _encodeRecord($value, $key = null) {
        if ('id' === $key) {
            return (string) $value;
        }
        if ($value instanceof DateTime) {
            return $value->format('c');
        }
        if ($value instanceof CM_Model_Abstract) {
            return [
                'class' => get_class($value),
                'id'    => (string) $value->getId(),
            ];
        }
        if (is_object($value)) {
            return [
                'class' => get_class($value),
            ];
        }
        if (is_array($value)) {
            $encoded = [];
            foreach ($value as $key => $val) {
                $encoded[$key] = $this->_encodeRecord($val, $key);
            }
            return $encoded;
        }
        return $value;
    }
}
