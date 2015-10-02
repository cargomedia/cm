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
        $dateFormat = null === $dateFormat ? 'd-m-Y H:i:s' : (string) $dateFormat;

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
        $data = [
            'datetime'  => $record->getCreatedAt()->format($this->_dateFormat),
            'levelname' => CM_Log_Logger::getLevelName($record->getLevel()),
            'message'   => $record->getMessage(),
        ];
        return preg_replace_callback('/\{([a-z]+)\}/i', function ($matches) use ($data) {
            return isset($data[$matches[1]]) ? $data[$matches[1]] : '';
        }, $this->_format);
    }

}
