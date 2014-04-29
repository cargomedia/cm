<?php

class CM_ViewResponse extends CM_DataResponse {

    /** @var string */
    private $_autoId;

    /** @var string */
    protected $_templateName = 'default';

    /** @var CM_View_Abstract */
    protected $_view;

    /** @var CM_ComponentFrontendHandler */
    protected $_js;

    /**
     * @param CM_View_Abstract $view
     */
    public function __construct(CM_View_Abstract $view) {
        $this->_view = $view;
        $this->_js = new CM_ComponentFrontendHandler();
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
        if (preg_match('/[^\w\.-]/', $name)) {
            throw new CM_Exception_Invalid('Invalid tpl-name `' . $name . '`');
        }
        $this->_templateName = $name;
    }

    public function getJs() {
        return $this->_js;
    }
}
