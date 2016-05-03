<?php

class CM_Log_Handler_Fluentd extends CM_Log_Handler_Abstract {

    /** @var \Fluent\Logger\FluentLogger */
    protected $_fluentLogger;

    /** @var string */
    protected $_tag;

    /** @var string */
    protected $_appName;

    /**
     * @param string   $server
     * @param int      $port
     * @param string   $tag
     * @param string   $appName
     * @param int|null $minLevel
     */
    public function __construct($server, $port, $tag, $appName, $minLevel = null) {
        parent::__construct($minLevel);
        $this->_fluentLogger = new Fluent\Logger\FluentLogger((string) $server, (int) $port);
        $this->_tag = (string) $tag;
        $this->_appName = (string) $appName;
    }

    /**
     * @return \Fluent\Logger\FluentLogger
     */
    protected function _getFluentd() {
        return $this->_fluentLogger;
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
        $context = $record->getContext();
        $levelsMapping = array_flip(CM_Log_Logger::getLevels());

        $formattedRecord = [
            'message'   => (string) $record->getMessage(),
            'level'     => strtolower($levelsMapping[$record->getLevel()]),
            'timestamp' => $record->getCreatedAt()->format(DateTime::ISO8601),
        ];
        if ($computerInfo = $context->getComputerInfo()) {
            $formattedRecord['computerInfo'] = [
                'fqdn'       => $computerInfo->getFullyQualifiedDomainName(),
                'phpVersion' => $computerInfo->getPhpVersion(),
            ];
        }

        $request = $context->getHttpRequest();
        if (null !== $request) {
            $serverArray = $request->getServer();
            $formattedRequest = [
                'uri'    => $request->getUri(),
                'method' => $request->getMethodName(),
            ];
            if (array_key_exists('http_referrer', $serverArray)) {
                $formattedRequest['referrer'] = (string) $serverArray['http_referrer'];
            }
            if (array_key_exists('http_user_agent', $serverArray)) {
                $formattedRequest['user_agent'] = (string) $serverArray['http_user_agent'];
            }
            if ($ip = $request->getIp()) {
                $formattedRequest['ip'] = (string) $ip;
            }
            $formattedRecord['request'] = $formattedRequest;
        }

        $appAttributes = $context->getExtra();
        if ($user = $context->getUser()) {
            $appAttributes['user'] = $user->getId();
        }
        if (null !== $request) {
            $appAttributes['clientId'] = $request->getClientId();
        }
        $formattedRecord[$this->_appName] = $appAttributes;

        if ($context->getAppContext()->hasException()) {
            $exception = $context->getAppContext()->getSerializableException();
            $formattedRecord['exception'] = [
                'type'    => $exception->getClass(),
                'message' => $exception->getMessage(),
                'stack'   => $exception->getTraceAsString(),
            ];
        }

        return $formattedRecord;
    }
}
