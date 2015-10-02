<?php

class CM_Log_Handler_Stream extends CM_Log_Handler_Abstract {

    /** @var  string */
    private $_format;

    /** @var  string */
    private $_dateFormat;

    /** @var resource */
    private $_stream;

    /**
     * @param CM_OutputStream_Interface $stream
     * @param int|null                  $level
     * @param string|null               $format     replace {datetime}, {levelname} and {message} by log record values
     * @param string|null               $dateFormat format accepted by date()
     */
    public function __construct(CM_OutputStream_Interface $stream, $level = null, $format = null, $dateFormat = null) {
        $format = null === $format ? '[{datetime} - {levelname}] {message}' : (string) $format;
        $dateFormat = null === $dateFormat ? 'c' : (string) $dateFormat;

        $this->_format = $format;
        $this->_dateFormat = $dateFormat;
        $this->_stream = $stream;

        parent::__construct($level);
    }

    /**
     * @param CM_Log_Record $record
     * @return bool
     */
    protected function _writeRecord(CM_Log_Record $record) {
        $this->_stream->writeln($this->_formatRecord($record));
    }

    /**
     * @param CM_Log_Record $record
     * @return string
     */
    protected function _formatRecord(CM_Log_Record $record) {
        $message = $this->_format($this->_format, [
            'datetime'  => $record->getCreatedAt()->format($this->_dateFormat),
            'levelname' => CM_Log_Logger::getLevelName($record->getLevel()),
            'message'   => $record->getMessage(),
        ]);

        $context = $record->getContext();
        $computerInfo = $context->getComputerInfo();
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
                'type'    => $server['REQUEST_METHOD'],
                'path'    => $httpRequest->getPath(),
                'proto'   => $server['SERVER_PROTOCOL'],
                'host'    => $httpRequest->getHost(),
                'ip'      => $httpRequest->getIp(),
                'referer' => $httpRequest->getHeader('referer'),
                'agent'   => $httpRequest->getHeader('user-agent'),
            ]);
        }
        if (!empty($extra)) {
            $extraText = [];
            foreach ($extra as $key => $value) {
                $extraText[] = sprintf('%s: %s', $key, $value);
            }
            $data['extra'] = implode(', ', $extraText);
        }
        if ($record instanceof CM_Log_Record_Exception) {
            $exception = $record->getException();

            $traceData = [];
            $traceCount = 0;
            foreach ($exception->getTrace() as $trace) {
                $traceData[] = sprintf('     %02d. %s %s:%s', $traceCount++, $trace['code'], $trace['file'], $trace['line']);
            }
            $traceText = implode(PHP_EOL, $traceData);

            $data['exception'] = PHP_EOL . $this->_formatArrayToLines('   - %s: %s', [
                    'message'    => $exception->getMessage(),
                    'type'       => $exception->getClass(),
                    'stacktrace' => PHP_EOL . $traceText,
                ]);
        }

        if(!empty($data)) {
            $message .= PHP_EOL . $this->_formatArrayToLines(' - %s: %s', $data);
        }
        return $message;
    }

    /**
     * @param string $text
     * @param array  $data
     * @return string
     */
    protected function _format($text, array $data) {
        $text = (string) $text;
        return preg_replace_callback('/\{([a-z]+)\}/i', function ($matches) use ($data) {
            return isset($data[$matches[1]]) ? $data[$matches[1]] : '';
        }, $text);
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
}
