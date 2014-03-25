<?php

class CM_ViewResponse extends CM_Class_Abstract {

    /** @var string */
    private $_autoId;

    /** @var string */
    protected $_templateName = 'default.tpl';

    /** @var array */
    protected $_templateParams = array();

    /** @var CM_ComponentFrontendHandler */
    protected $_frontendHandler = null;

    public function __construct() {
        $this->_frontendHandler = new CM_ComponentFrontendHandler();
    }

    /**
     * @param string $id_value
     * @return string
     */
    public function getTagAutoId($id_value) {
        return $this->getAutoId() . '-' . $id_value;
    }

    /**
     * @return string
     */
    public function getAutoId() {
        if (!$this->_autoId) {
            $this->_autoId = uniqid();
        }
        return $this->_autoId;
    }

    /**
     * @return CM_ComponentFrontendHandler
     */
    public function getFrontendHandler() {
        return $this->_frontendHandler;
    }

    /**
     * @param string $key
     * @param mixed  $value
     */
    public function setTplParam($key, $value) {
        $this->_templateParams[$key] = $value;
    }

    /**
     * @return array
     */
    public function getTemplateParams() {
        return $this->_templateParams;
    }

    /**
     * @return string
     */
    public function getTemplateName() {
        return $this->_templateName;
    }

    /**
     * @param string $filename
     * @throws CM_Exception_Invalid
     */
    public function setTemplateName($filename) {
        $filename = (string) $filename . '.tpl';
        if (preg_match('/[^\w\.-]/', $filename)) {
            throw new CM_Exception_Invalid('Invalid tpl-name `' . $filename . '`');
        }
        $this->_templateName = $filename;
    }
}
