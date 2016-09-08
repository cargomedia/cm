<?php

class CM_Log_Handler_MongoDb extends CM_Log_Handler_Abstract {

    const DEFAULT_TYPE = 0;

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
        if (!isset($extra['type'])) {
            $extra['type'] = self::DEFAULT_TYPE;
        }
        $formattedContext['extra'] = $extra;
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

            $formattedContext['httpRequest']['query'] = $request->findQuery();

            if ($request instanceof CM_Http_Request_Post) {
                $formattedContext['httpRequest']['body'] = $request->getBody();
            }

            $formattedContext['httpRequest']['clientId'] = $request->getClientId();
        }

        if ($exception = $recordContext->getException()) {
            $serializableException = new CM_ExceptionHandling_SerializableException($exception);
            $formattedContext['exception'] = [
                'class'       => $serializableException->getClass(),
                'message'     => $serializableException->getMessage(),
                'line'        => $serializableException->getLine(),
                'file'        => $serializableException->getFile(),
                'trace'       => $serializableException->getTrace(),
                'traceString' => $serializableException->getTraceAsString(),
                'meta'        => $serializableException->getMeta(),
            ];
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
