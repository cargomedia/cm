<?php

class CM_Log_ContextFormatter_MongoDb implements CM_Log_ContextFormatter_Interface {

    const DEFAULT_TYPE = 0;

    public function formatContext(CM_Log_Context $context) {
        $computerInfo = $context->getComputerInfo();
        $user = $context->getUser();
        $extra = $context->getExtra();
        $request = $context->getHttpRequest();

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
        $formattedContext['extra'] = $this->_encodeExtra($extra);
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

        if ($exception = $context->getException()) {
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

        return $formattedContext;
    }

    public function formatAppContext(CM_Log_Context $context) {
        throw new CM_Exception_NotImplemented();
    }

    /**
     * @param array $extra
     * @return array
     */
    protected function _encodeExtra(array $extra) {
        array_walk_recursive($extra, function (&$value) {
            $encoded = $value;
            if ($value instanceof DateTime) {
                $encoded = new MongoDate($value->getTimestamp());
            } elseif ($value instanceof CM_Model_Abstract) {
                $encoded = '[' . get_class($value) . ':' . $value->getId() . ']';
            } elseif ($value instanceof JsonSerializable) {
                $encoded = $value->jsonSerialize();
                if (is_array($encoded)) {
                    $encoded = $this->_encodeExtra($encoded);
                }
            } elseif (is_object($value)) {
                $encoded = '[' . get_class($value) . ']';
            }
            $value = $encoded;
        });
        return $extra;
    }
}
