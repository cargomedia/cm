<?php

class CM_Log_Handler_MongoDb extends CM_Log_Handler_Abstract {

    /** @var  string */
    protected $_collection;

    /** @var int|null */
    protected $_recordTtl = null;

    /** @var CM_Log_ContextFormatter_Interface */
    protected $_contextFormatter;

    /** @var  CM_MongoDb_Client */
    protected $_mongoDb;

    /** @var  array */
    protected $_insertOptions;

    /**
     * CM_Log_Handler_MongoDb constructor.
     * @param CM_MongoDb_Client                 $client
     * @param CM_Log_ContextFormatter_Interface $contextFormatter
     * @param string                            $collection
     * @param int|null                          $recordTtl Time To Live in seconds
     * @param array|null                        $insertOptions
     * @param int|null                          $minLevel
     * @throws CM_Exception_Invalid
     */
    public function __construct(CM_MongoDb_Client $client, CM_Log_ContextFormatter_Interface $contextFormatter, $collection,
                                $recordTtl = null, array $insertOptions = null, $minLevel = null) {
        parent::__construct($minLevel);
        $this->_contextFormatter = $contextFormatter;
        $this->_collection = (string) $collection;
        $this->_mongoDb = $client;
        if (null !== $recordTtl) {
            $this->_recordTtl = (int) $recordTtl;
            if ($this->_recordTtl <= 0) {
                throw new CM_Exception_Invalid('TTL should be positive value');
            }
        };
        $this->_insertOptions = null !== $insertOptions ? $insertOptions : ['w' => 0];
    }

    /**
     * @param CM_Log_Record $record
     */
    protected function _writeRecord(CM_Log_Record $record) {
        /** @var array $formattedRecord */
        $formattedRecord = $this->_formatRecord($record);
        $this->_mongoDb->insert($this->_collection, $formattedRecord, $this->_insertOptions);
    }

    /**
     * @param CM_Log_Record $record
     * @return array
     */
    protected function _formatRecord(CM_Log_Record $record) {
        $createdAt = $record->getCreatedAt();
        $formattedRecord = [
            'level'     => (int) $record->getLevel(),
            'message'   => (string) $record->getMessage(),
            'createdAt' => new MongoDate($createdAt->getTimestamp()),
            'context'   => $this->_contextFormatter->formatContext($record->getContext()),
        ];

        if (null !== $this->_recordTtl) {
            $expireAt = clone $createdAt;
            $expireAt->add(new DateInterval('PT' . $this->_recordTtl . 'S'));
            $formattedRecord['expireAt'] = new MongoDate($expireAt->getTimestamp());
        }

        $formattedRecord = $this->_sanitizeRecord($formattedRecord); //TODO remove after investigation
        return $formattedRecord;
    }

    /**
     * @param array $formattedRecord
     * @return array
     */
    protected function _sanitizeRecord(array $formattedRecord) {
        $nonUtfBytesList = [];
        array_walk_recursive($formattedRecord, function (&$value, $key) use (&$nonUtfBytesList) {
            if (is_string($value) && !mb_check_encoding($value, 'UTF-8')) {
                $nonUtfBytesList[$key] = unpack('H*', $value)[1];
                $value = CM_Util::sanitizeUtf($value);
            }
        });

        if (!empty($nonUtfBytesList)) {
            $formattedRecord['loggerNotifications']['sanitizedFields'] = [];
            foreach ($nonUtfBytesList as $key => $nonUtfByte) {
                $formattedRecord['loggerNotifications']['sanitizedFields'][$key] = $nonUtfByte;
            }
        }
        return $formattedRecord;
    }
}
