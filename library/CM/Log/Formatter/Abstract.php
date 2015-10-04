<?php

abstract class CM_Log_Formatter_Abstract implements CM_Log_Formatter_Interface {

    /** @var  string */
    protected $_formatMessage;

    /** @var  string */
    protected $_formatDate;

    /**
     * @param string|null $formatMessage replace {datetime}, {levelName}, {fqdn}, {phpversion} and {message} by log record values
     * @param string|null $formatDate    format accepted by date()
     */
    public function __construct($formatMessage = null, $formatDate = null) {
        $defaults = $this->_getDefaults();

        $formatMessage = null === $formatMessage ? $defaults['formatMessage'] : (string) $formatMessage;
        $formatDate = null === $formatDate ? $defaults['formatDate'] : (string) $formatDate;

        $this->_formatMessage = $formatMessage;
        $this->_formatDate = $formatDate;
    }

    abstract public function renderMessage(CM_Log_Record $record);

    abstract public function renderContext(CM_Log_Record $record);

    abstract public function renderException(CM_Log_Record_Exception $record);

    /**
     * @return array
     */
    protected function _getDefaults() {
        return [
            'formatMessage' => '{message}',
            'formatDate'    => 'c',
        ];
    }

    /**
     * @param CM_Log_Record $record
     * @return array
     * @throws CM_Exception_Invalid
     */
    protected function _getRecordInfo(CM_Log_Record $record) {
        $computerInfo = $record->getContext()->getComputerInfo();
        return [
            'datetime'   => $record->getCreatedAt()->format($this->_formatDate),
            'levelname'  => CM_Log_Logger::getLevelName($record->getLevel()),
            'message'    => $record->getMessage(),
            'fqdn'       => null === $computerInfo ? 'none' : $computerInfo->getFullyQualifiedDomainName(),
            'phpVersion' => null === $computerInfo ? 'none' : $computerInfo->getPhpVersion(),
        ];
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
}
