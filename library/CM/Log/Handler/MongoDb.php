<?php

class CM_Log_Handler_MongoDb extends CM_Log_Handler_Abstract {

    /** @var  string */
    protected $_collection;

    /** @var int|null */
    protected $_recordTtl = null;

    /** @var  CM_MongoDb_Client */
    protected $_mongoDb;

    /** @var  array */
    protected $_insertOptions;

    /**
     * @param string   $collection
     * @param int|null $recordTtl Time To Live in seconds
     * @param array    $insertOptions
     * @param int|null $minLevel
     * @throws CM_Exception_Invalid
     */
    public function __construct($collection, $recordTtl = null, array $insertOptions = null, $minLevel = null) {
        parent::__construct($minLevel);
        $this->_collection = (string) $collection;
        $this->_mongoDb = CM_Service_Manager::getInstance()->getMongoDb();
        if (null !== $recordTtl) {
            $this->_recordTtl = (int) $recordTtl;
            if ($this->_recordTtl <= 0) {
                throw new CM_Exception_Invalid('TTL should be positive value');
            }
        };

        $this->_insertOptions = null !== $insertOptions ? $insertOptions : ['w' => 0];
        $this->_validateCollection($this->_collection);
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
        $recordContext = $record->getContext();

        $computerInfo = $recordContext->getComputerInfo();
        $user = $recordContext->getUser();
        $extra = $recordContext->getExtra();
        $request = $recordContext->getHttpRequest();

        $createdAt = $record->getCreatedAt();

        $formattedContext = [];
        if (null !== $computerInfo) {
            $formattedContext['computerInfo'] = [
                'fqdn'       => $computerInfo->getFullyQualifiedDomainName(),
                'phpVersion' => $computerInfo->getPhpVersion(),
            ];
        }
        if (null !== $extra) {
            $formattedContext['extra'] = $extra;
        }
        if (null !== $user) {
            $formattedContext['user'] = [
                'id'   => $user->getId(),
                'name' => $user->getDisplayName(),
            ];
        }
        if (null !== $request) {
            $formattedContext['httpRequest'] = [
                'method'  => $request->getMethodName(),
                'uri'     => $request->getUri(),
                'query'   => [],
                'server'  => $request->getServer(),
                'headers' => $request->getHeaders(),
            ];

            try {
                $formattedContext['httpRequest']['query'] = $request->getQuery();
            } catch (CM_Exception_Invalid $e) {
                //CM_Http_Request_Post can throw.
            }

            if ($request instanceof CM_Http_Request_Post) {
                $formattedContext['httpRequest']['body'] = $request->getBody();
            }

            $formattedContext['httpRequest']['clientId'] = $request->getClientId();
        }

        if ($recordContext->getAppContext()->hasException()) {
            $formattedContext['exception'] = $recordContext->getAppContext()->getSerializableException();
        }

        $formattedRecord = [
            'level'     => (int) $record->getLevel(),
            'message'   => (string) $record->getMessage(),
            'createdAt' => new MongoDate($createdAt->getTimestamp()),
            'context'   => $formattedContext,
        ];

        if (null !== $this->_recordTtl) {
            $expireAt = clone $createdAt;
            $expireAt->add(new DateInterval('PT' . $this->_recordTtl . 'S'));
            $formattedRecord['expireAt'] = new MongoDate($expireAt->getTimestamp());
        }

        return $formattedRecord;
    }

    /**
     * @param string $collection
     * @throws CM_Exception_Invalid
     */
    protected function _validateCollection($collection) {
        if (null !== $this->_recordTtl) {
            $indexInfo = $this->_mongoDb->getIndexInfo($collection);
            $foundIndex = \Functional\some($indexInfo, function ($el) {
                return isset($el['key']['expireAt']) && isset($el['expireAfterSeconds']) && $el['expireAfterSeconds'] == 0;
            });

            if (!$foundIndex) {
                throw new CM_Exception_Invalid('MongoDb Collection `' . $collection . '` does not contain valid TTL index');
            };
        }
    }
}
