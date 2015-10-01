<?php

class CM_Log_Handler_Stream extends CM_Log_Handler_Abstract {

    /** @var  string */
    private $_format;

    /** @var  string */
    private $_dateFormat;

    /** @var  bool */
    private $_useLock;

    /** @var resource */
    private $_filename;

    /**
     * @param string      $name
     * @param string      $filename   with the form "scheme://..."
     * @param int|null    $level
     * @param string|null $format     replace {datetime}, {levelname} and {message} by log record values
     * @param string|null $dateFormat format accepted by date()
     * @param bool|null   $useLock
     */
    public function __construct($name, $filename, $level = null, $format = null, $dateFormat = null, $useLock = null) {
        $format = null === $format ? '[{datetime} - {levelname}] {message}' : (string) $format;
        $dateFormat = null === $dateFormat ? 'd-m-Y H:i:s' : (string) $dateFormat;
        $useLock = null === $useLock ? false : (bool) $useLock;
        $name = (string) $name;
        $filename = (string) $filename;

        $this->_format = $format;
        $this->_dateFormat = $dateFormat;
        $this->_useLock = $useLock;
        $this->_filename = $filename;

        parent::__construct($name, $level);
    }

    /**
     * @param CM_Log_Record $record
     * @return bool
     */
    protected function _writeRecord(CM_Log_Record $record) {
        $stream = fopen($this->_filename, 'w+');
        if ($this->_useLock) {
            flock($stream, LOCK_EX);
        }
        fwrite($stream, $this->_formatRecord($record) . PHP_EOL);
        if ($this->_useLock) {
            flock($stream, LOCK_UN);
        }
        fclose($stream);
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
