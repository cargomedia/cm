<?php

abstract class CM_FormField_Abstract extends CM_View_Abstract {

    /** @var mixed */
    private $_value;

    /** @var string */
    protected $_name;

    /**
     * @var array
     */
    protected $_options = array();

    /**
     * @param CM_Frontend_Environment $environment
     * @param string|array            $userInput
     * @return mixed
     */
    abstract public function validate(CM_Frontend_Environment $environment, $userInput);

    public function __construct($params = null) {
        parent::__construct($params);
        $this->_name = $this->_params->getString('name', uniqid());
        $this->_initialize();
    }

    protected function _initialize() {
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->_name;
    }

    /**
     * @return mixed|null Internal value
     */
    public function getValue() {
        return $this->_value;
    }

    /**
     * @return array
     */
    public function getOptions() {
        return $this->_options;
    }

    /**
     * @param mixed $value Internal value
     */
    public function setValue($value) {
        $this->_value = $value;
    }

    /**
     * @param string $userInput
     * @return bool
     */
    public function isEmpty($userInput) {
        if (is_array($userInput) && !empty($userInput)) {
            return false;
        }
        if (is_scalar($userInput) && strlen(trim($userInput)) > 0) {
            return false;
        }
        return true;
    }

    /**
     * Filter-out invalid input data depending on of the field type.
     * For text field this will remove invalid UTF8 chars etc.
     * @param mixed $userInput
     * @return mixed
     */
    public function filterInput($userInput) {
        return $userInput;
    }

    /**
     * @param CM_Params                $renderParams
     * @param CM_Frontend_ViewResponse $viewResponse
     */
    public function prepare(CM_Params $renderParams, CM_Frontend_ViewResponse $viewResponse) {
    }

    public function ajax_validate(CM_Params $params, CM_Frontend_JavascriptContainer_View $handler, CM_Http_Response_View_Ajax $response) {
        $environment = $response->getRender()->getEnvironment();
        $userInput = $params->get('userInput');
        $this->validate($environment, $userInput);
    }
}
