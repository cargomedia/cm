<?php

class CM_ViewResponse extends CM_Class_Abstract {

    /** @var string */
    private $_autoId;

    /** @var string */
    protected $_templateName = 'default.tpl';

    /** @var array */
    protected $_data = array();

    /** @var CM_ComponentFrontendHandler */
    protected $_frontendHandler = null;

    /** @var CM_View_Abstract */
    protected $_view;

    /**
     * @param CM_View_Abstract $view
     */
    public function __construct(CM_View_Abstract $view) {
        $this->_frontendHandler = new CM_ComponentFrontendHandler();
        $this->_view = $view;
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
    public function addData($key, $value) {
        $this->_data[$key] = $value;
    }

    /**
     * @param array $data
     */
    public function setData(array $data) {
        $this->_data = $data;
    }

    /**
     * @return array
     */
    public function getData() {
        return $this->_data;
    }

    /**
     * @return string
     */
    public function getTemplateName() {
        return $this->_templateName;
    }

    /**
     * @return CM_View_Abstract
     */
    public function getView() {
        return $this->_view;
    }

    /**
     * @param string $name
     * @throws CM_Exception_Invalid
     */
    public function setTemplateName($name) {
        $name = (string) $name . '.tpl';
        if (preg_match('/[^\w\.-]/', $name)) {
            throw new CM_Exception_Invalid('Invalid tpl-name `' . $name . '`');
        }
        $this->_templateName = $name;
    }

    /**
     * @param string $code
     */
    public function callJs($code) {
        $this->getFrontendHandler()->exec($code);
    }
}
