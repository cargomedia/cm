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
        if (null !== $severity) {
            $this->setSeverity((int) $severity);
        }
        $this->_metaInfo = null !== $metaInfo ? $metaInfo : array();
        if (isset($options['messagePublic'])) {
            $this->_setMessagePublic($options['messagePublic']);
        }
        parent::__construct($message);
    }

    /**
     * @param CM_Frontend_Render $render
     * @return array
     */
    public function getClientData(CM_Frontend_Render $render) {
        return [
            'type'     => get_class($this),
            'msg'      => $this->getMessagePublic($render),
            'isPublic' => $this->isPublic(),
            'metaInfo' => [],
        ];
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
            throw new CM_Exception_Invalid('Invalid severity', null, ['severity' => $severity]);
        }
        $this->_severity = (int) $severity;
    }

    /**
     * @return mixed[]
     */
    public function getMetaInfo() {
        return $this->_metaInfo;
    }

    /**
     * @param CM_I18n_Phrase $messagePublic
     */
    protected function _setMessagePublic(CM_I18n_Phrase $messagePublic) {
        $this->_messagePublic = $messagePublic;
    }
}
