<?php

class CM_Log_ContextFormatter_Cargomedia {

    /**
     * @param string $appName
     */
    public function __construct($appName) {
        $this->_appName = $appName;
    }

    /**
     * @param CM_Log_Record $record
     * @return array
     */
    public function getRecordContext(CM_Log_Record $record) {
        $levelsMapping = array_flip(CM_Log_Logger::getLevels());
        $context = $record->getContext();

        $hash = [
            'message'   => (string) $record->getMessage(),
            'level'     => strtolower($levelsMapping[$record->getLevel()]),
            'timestamp' => $record->getCreatedAt()->format(DateTime::ISO8601),
        ];
        return array_merge($hash, $this->getContext($context));
    }

    /**
     * @param CM_Log_Context $context
     * @return array
     */
    public function getContext(CM_Log_Context $context) {
        $hash = [];
        if ($computerInfo = $context->getComputerInfo()) {
            $hash['computerInfo'] = [
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
            if ($request->hasHeader('host')) {
                $formattedRequest['hostname'] = $request->getHost();
            }
            $formattedRecord['httpRequest'] = $formattedRequest;
        }
        $hash = array_merge($hash, $this->getAppContext($context));

        if ($exception = $context->getException()) {
            $serializableException = new CM_ExceptionHandling_SerializableException($exception);
            $formattedRecord['exception'] = [
                'type'    => $serializableException->getClass(),
                'message' => $serializableException->getMessage(),
                'stack'   => $serializableException->getTraceAsString(),
            ];
        }
        return $hash;
    }

    /**
     * @param CM_Log_Context $context
     * @return array
     * @throws CM_Exception_Invalid
     */
    public function getAppContext(CM_Log_Context $context) {
        $appAttributes = $context->getExtra();
        if ($user = $context->getUser()) {
            $appAttributes['user'] = $user->getId();
        }
        $request = $context->getHttpRequest();
        if (null !== $request) {
            $appAttributes['clientId'] = $request->getClientId();
        }
        return [$this->_appName => $appAttributes];
    }
}
