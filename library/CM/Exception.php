<?php

class CM_Exception extends Exception {

    const WARN = 1;
    const ERROR = 2;
    const FATAL = 3;

    /** @var string|null */
    private $_messagePublic;

    /** @var array|null */
    private $_messagePublicVariables;

    /** @var int */
    protected $_severity = self::ERROR;

    /** @var array */
    private $_metaInfo;

    /**
     * @param string|null $message
     * @param array|null  $metaInfo
     * @param array|null  $options
     */
    public function __construct($message = null, array $metaInfo = null, array $options = null) {
        $this->_metaInfo = null !== $metaInfo ? $metaInfo : array();
        $this->_messagePublic = isset($options['messagePublic']) ? (string) $options['messagePublic'] : null;
        $this->_messagePublicVariables = isset($options['messagePublicVariables']) ? (array) $options['messagePublicVariables'] : null;
        if (isset($options['severity'])) {
            $this->setSeverity($options['severity']);
        }
        parent::__construct($message);
    }

    /**
     * @param CM_Render $render
     * @return string
     */
    public function getMessagePublic(CM_Render $render) {
        if (!$this->isPublic()) {
            return 'Internal server error';
        }
        return $render->getTranslation($this->_messagePublic, $this->_messagePublicVariables);
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
     * @return string[]
     */
    public function getMetaInfo() {
        return Functional\map($this->_metaInfo, function ($value) {
            return CM_Util::varDump($value);
        });
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
}
