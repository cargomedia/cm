<?php

class CM_Exception extends Exception {

    const WARN = 1;
    const ERROR = 2;
    const FATAL = 3;

    /** @var int */
    protected $_severity = self::ERROR;

    /** @var array */
    private $_metaInfo;

    /** @var CM_I18n_Phrase|null */
    private $_messagePublic;

    /**
     * @param string|null $message
     * @param int|null    $severity
     * @param array|null  $metaInfo
     * @param array|null  $options
     */
    public function __construct($message = null, $severity = null, array $metaInfo = null, array $options = null) {
        $this->_validateOptions($options);

        $this->_metaInfo = null !== $metaInfo ? $metaInfo : array();
        if (null !== $severity) {
            $this->setSeverity((int) $severity);
        }
        if (isset($options['messagePublic'])) {
            $this->_messagePublic = $options['messagePublic'];
        }
        parent::__construct($message);
    }

    /**
     * @param CM_Frontend_Render $render
     * @return string
     */
    public function getMessagePublic(CM_Frontend_Render $render) {
        if (!$this->isPublic()) {
            return 'Internal server error';
        }
        return $this->_messagePublic->translate($render);
    }

    /**
     * @return boolean
     */
    public function isPublic() {
        return (null !== $this->_messagePublic);
    }

    /**
     * @return int
     */
    public function getSeverity() {
        return $this->_severity;
    }

    /**
     * @param int $severity
     * @throws CM_Exception_Invalid
     */
    public function setSeverity($severity) {
        if (!in_array($severity, array(self::WARN, self::ERROR, self::FATAL), true)) {
            throw new CM_Exception_Invalid('Invalid severity `' . $severity . '`');
        }
        $this->_severity = $severity;
    }

    /**
     * @return mixed[]
     */
    public function getMetaInfo() {
        return $this->_metaInfo;
    }

    /**
     * @return CM_Paging_Log_Error|CM_Paging_Log_Fatal|CM_Paging_Log_Warn
     */
    public function getLog() {
        switch ($this->getSeverity()) {
            case self::WARN:
                return new CM_Paging_Log_Warn();
                break;
            case self::ERROR:
                return new CM_Paging_Log_Error();
                break;
            case self::FATAL:
            default:
                return new CM_Paging_Log_Fatal();
                break;
        }
    }

    /**
     * @param array|null $options
     * @throws CM_Exception_InvalidParam
     */
    private function _validateOptions(array $options = null) {
        if (is_null($options)) {
            return;
        }

        $validKeysToTypes = [
            'messagePublic' => 'CM_I18n_Phrase',
        ];

        if (count($validKeysToTypes) < count($options)) {
            throw new CM_Exception_InvalidParam('$options parameter contains invalid key(s)');
        }

        foreach ($options as $optionKey => $optionValue) {
            if (!array_key_exists($optionKey, $validKeysToTypes)) {
                throw new CM_Exception_InvalidParam('Invalid key for $options: `' . $optionKey . '`');
            } elseif ($validKeysToTypes[$optionKey] !== null && !($options[$optionKey] instanceof $validKeysToTypes[$optionKey])) {
                throw new CM_Exception_InvalidParam('Invalid type for key `' . $optionKey . '`');
            }
        }
    }
}
