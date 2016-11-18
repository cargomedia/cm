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

    public function formatContext(CM_Log_Context $context) {
        $result = [];
        if ($computerInfo = $context->getComputerInfo()) {
            $result['computerInfo'] = [
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
            $query = $request->findQuery();
            unset($query['viewInfoList']);
            $formattedRequest['query'] = CM_Util::jsonEncode($query, true);
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
            $result['httpRequest'] = $formattedRequest;
        }
        $result = array_merge($result, $this->formatAppContext($context));

        if ($exception = $context->getException()) {
            $serializableException = new CM_ExceptionHandling_SerializableException($exception);
            $stack = $serializableException->getTrace();
            if (!empty($stack)) {
                $stackAsString = trim(Functional\reduce_left(array_reverse($stack), function ($value, $index, $collection, $reduction) {
                    return $reduction . '#' . $index . ' ' . $value['file'] . '(' . $value['line'] . '): ' . $value['code'] . PHP_EOL;
                }, ''));
            } else {
                $stackAsString = $serializableException->getTraceAsString();
            }
            $result['exception'] = [
                'type'     => $serializableException->getClass(),
                'message'  => $serializableException->getMessage(),
                'stack'    => $stackAsString,
                'metaInfo' => $serializableException->getMeta(),
            ];
        }
        return $result;
    }

    public function formatAppContext(CM_Log_Context $context) {
        $result = [];
        $appAttributes = $context->getExtra();
        if ($user = $context->getUser()) {
            $appAttributes['user'] = [
                'id'          => $user->getId(),
                'displayName' => $user->getDisplayName(),
            ];
        }
        $request = $context->getHttpRequest();
        if (null !== $request) {
            $appAttributes['client'] = [
                'id' => $request->getClientId(),
            ];
        }
        $result[$this->_appName] = $appAttributes;
        return $result;
    }
}
