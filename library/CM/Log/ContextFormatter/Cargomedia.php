<?php

class CM_Log_ContextFormatter_Cargomedia implements CM_Log_ContextFormatter_Interface {
    
    /** @var string */
    protected $_appName;

    /**
     * @param string $appName
     */
    public function __construct($appName) {
        $this->_appName = (string) $appName;
    }

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
            if (array_key_exists('http_referer', $serverArray)) {
                $formattedRequest['referer'] = (string) $serverArray['http_referer'];
            }
            if (array_key_exists('http_user_agent', $serverArray)) {
                $formattedRequest['useragent'] = (string) $serverArray['http_user_agent'];
            }
            if ($ip = $request->getIp()) {
                $formattedRequest['ip'] = (string) $ip;
            }
            if ($request->hasHeader('host')) {
                $formattedRequest['hostname'] = $request->getHost();
            }
            $hash['httpRequest'] = $formattedRequest;
        }
        $hash = array_merge($hash, $this->getAppContext($context));

        if ($exception = $context->getException()) {
            $serializableException = new CM_ExceptionHandling_SerializableException($exception);
            $hash['exception'] = [
                'type'    => $serializableException->getClass(),
                'message' => $serializableException->getMessage(),
                'stack'   => $serializableException->getTraceAsString(),
            ];
        }
        return $hash;
    }

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
