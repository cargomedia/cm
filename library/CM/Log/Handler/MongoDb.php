<?php

class CM_Log_Handler_MongoDb extends CM_Log_Handler_Abstract {

    /** @var  string */
    protected $_collection;

    /** @var int */
    protected $_recordTtl;

    /** @var  CM_MongoDb_Client */
    protected $_mongoDb;

    /**
     * @param string   $collection
     * @param int|null $recordTtl Time To Live in seconds
     * @param int|null $level
     */
    public function __construct($collection, $recordTtl = null, $level = null) {
        parent::__construct($level);
        $this->_collection = (string) $collection;
        $this->_recordTtl = null === $recordTtl ? 3600 * 30 * 2 : (int) $recordTtl;

        $this->_mongoDb = CM_Service_Manager::getInstance()->getMongoDb();
    }

    /**
     * @param CM_Log_Record $record
     */
    protected function _writeRecord(CM_Log_Record $record) {
        $expireAt = $record->getCreatedAt();
        $expireAt->add(new DateInterval('PT' . $this->_recordTtl . 'S'));

        /** @var array $formattedRecord */
        $formattedRecord = json_decode(json_encode($record), true);
        $formattedRecord['expireAt'] = new MongoDate($expireAt->format(DateTime::ISO8601));

        $this->_mongoDb->insert($this->_collection, $formattedRecord);
    }
}
