<?php

class CM_Log_Formatter_Text extends CM_Log_Formatter_Abstract {

    public function renderMessage(CM_Log_Record $record) {
        return $this->_format($this->_formatMessage, $this->_getRecordInfo($record));
    }

    public function renderContext(CM_Log_Record $record) {
        $context = $record->getContext();
        $user = $context->getUser();
        $httpRequest = $context->getHttpRequest();
        $extra = $context->getExtra();

        $data = [];

        if (null !== $user) {
            $data['user'] = $this->_format('id: {id}, email: {email}', [
                'id'    => $user->getId(),
                'email' => $user->getEmail(),
            ]);
        }
        if (null != $httpRequest) {
            $server = $httpRequest->getServer();
            $httpRequestText = '{type} {path} {proto}, host: {host}, ip: {ip}, referer: {referer}, user-agent: {agent}';
            $data['httpRequest'] = $this->_format($httpRequestText, [
                'type'    => isset($server['REQUEST_METHOD']) ? $server['REQUEST_METHOD'] : '',
                'path'    => $httpRequest->getPath(),
                'proto'   => isset($server['SERVER_PROTOCOL']) ? $server['SERVER_PROTOCOL'] : '',
                'host'    => $httpRequest->getHost(),
                'ip'      => $httpRequest->getIp(),
                'referer' => $httpRequest->getHeader('referer'),
                'agent'   => $httpRequest->getUserAgent(),
            ]);
        }
        if (!empty($extra)) {
            $extraText = [];
            foreach ($extra as $key => $value) {
                $extraText[] = sprintf('%s: %s', $key, $value);
            }
            $data['extra'] = implode(', ', $extraText);
        }
        if ($record->getContext()->getAppContext()->hasException()) {
            $data['exception'] = $this->_renderException($record->getContext()->getAppContext()->getSerializableException());
        }
        $output = empty($data) ? null : $this->_formatArrayToLines(' - %s: %s', $data);
        return $output;
    }

    /**
     * @param string $format
     * @param array  $data
     * @return string
     */
    protected function _formatArrayToLines($format, array $data) {
        $format = (string) $format;
        $dataText = [];
        foreach ($data as $key => $value) {
            $dataText[] = sprintf($format, $key, $value);
        }
        return implode(PHP_EOL, $dataText);
    }

    /**
     * @param CM_ExceptionHandling_SerializableException $exception
     * @return string
     */
    protected function _renderException(CM_ExceptionHandling_SerializableException $exception) {
        $traceData = [];
        $traceCount = 0;
        foreach ($exception->getTrace() as $trace) {
            $traceData[] = sprintf('     %02d. %s %s:%s', $traceCount++, $trace['code'], $trace['file'], $trace['line']);
        }
        $traceText = implode(PHP_EOL, $traceData);

        return PHP_EOL . $this->_formatArrayToLines('   - %s: %s', [
            'message'    => $exception->getMessage(),
            'type'       => $exception->getClass(),
            'stacktrace' => PHP_EOL . $traceText,
        ]);
    }

    protected function _getDefaults() {
        return [
            'formatMessage' => '[{datetime} - {fqdn} - php {phpVersion} - {levelname}] {message}',
            'formatDate'    => 'c',
        ];
    }
}
