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
        $this->_mongoDb = CM_Service_Manager::getInstance()->getMongoDb();
        //TODO check index existance.
        $this->_mongoDb->getIndexInfo($this->_collection);

        $this->_recordTtl = null === $recordTtl ? 3600 * 30 * 2 : (int) $recordTtl;
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

    /**
     * @param CM_Log_Record $record
     * @return array
     */
    protected function formatRecord(CM_Log_Record $record) {
        $recordContext = $record->getContext();
        $computerInfo = $recordContext->getComputerInfo();
        $user = $recordContext->getUser();
        $extra = $recordContext->getExtra();

        $formattedContext = [];
        if (null !== $computerInfo) {
            $formattedContext['computerInfo'] = [
                'fqdn' => $computerInfo->getFullyQualifiedDomainName(),
                'phpVersion' => $computerInfo->getPhpVersion(),
            ];
        }
        return [
            'level'     => (int) $record->getLevel(),
            'message'   => (string) $record->getMessage(),
            'createdAt' => new MongoDate($record->getCreatedAt()->getTimestamp()),
            'context'   => $formattedContext,
        ];
    }
}
