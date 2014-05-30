<?php

abstract class CM_Form_Abstract extends CM_View_Abstract {

    /** @var string */
    private $_name;

    /** @var array */
    private $_fields = array();

    /** @var CM_FormAction_Abstract[] */
    private $_actions = array();

    public function __construct($params = null) {
        parent::__construct($params);

        if (!preg_match('/^\w+_Form_(.+)$/', get_class($this), $matches)) {
            throw new CM_Exception("Cannot detect namespace from forms class-name");
        }
        $name = lcfirst($matches[1]);
        $name = preg_replace('/([A-Z])/', '_\1', $name);
        $name = strtolower($name);
        $this->_name = $name;
    }

    abstract public function initialize();

    /**
     * @param CM_Params $renderParams
     */
    public function prepare(CM_Params $renderParams) {
    }

    /**
     * @param CM_FormField_Abstract $field
     * @throws CM_Exception_Invalid
     */
    protected function registerField(CM_FormField_Abstract $field) {
        $fieldName = $field->getName();
        if (isset($this->_fields[$fieldName])) {
            throw new CM_Exception_Invalid('Form field `' . $fieldName . '` is already registered.');
        }

        $this->_fields[$fieldName] = $field;
    }

    /**
     * @param CM_FormAction_Abstract $action
     * @throws CM_Exception_Invalid
     */
    protected function registerAction(CM_FormAction_Abstract $action) {
        $actionName = $action->getName();
        if (isset($this->_actions[$actionName])) {
            throw new CM_Exception_Invalid('Form action `' . $actionName . '` is already registered.');
        }
        $this->_actions[$actionName] = $action;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->_name;
    }

    /**
     * @return CM_FormAction_Abstract[]
     */
    public function getActions() {
        return $this->_actions;
    }

    /**
     * @param string $name
     * @return CM_FormAction_Abstract
     * @throws CM_Exception_Invalid
     */
    public function getAction($name) {
        if (!isset($this->_actions[$name])) {
            throw new CM_Exception_Invalid('Unrecognized action `' . $name . '`.');
        }
        return $this->_actions[$name];
    }

    /**
     * @return CM_FormField_Abstract[]
     */
    public function getFields() {
        return $this->_fields;
    }

    /**
     * @param string $fieldName
     * @return CM_FormField_Abstract
     * @throws CM_Exception_Invalid
     */
    public function getField($fieldName) {
        if (!isset($this->_fields[$fieldName])) {
            throw new CM_Exception_Invalid('Unrecognized field `' . $fieldName . '`.');
        }
        return $this->_fields[$fieldName];
    }

    /**
     * @param array                 $data
     * @param string                $actionName
     * @param CM_Response_View_Form $response
     * @return mixed
     */
    public function process(array $data, $actionName, CM_Response_View_Form $response) {
        $action = $this->getAction($actionName);

        $formData = array();
        foreach ($action->getFieldList() as $fieldName => $required) {
            $field = $this->getField($fieldName);

            $isEmpty = true;
            if (array_key_exists($fieldName, $data)) {
                $fieldValue = $field->filterInput($data[$fieldName]);

                if (!$field->isEmpty($fieldValue)) {
                    $isEmpty = false;
                    $environment = $response->getRender()->getEnvironment();
                    try {
                        $formData[$fieldName] = $field->validate($environment, $fieldValue, $response);
                    } catch (CM_Exception_FormFieldValidation $e) {
                        $response->addError($e->getMessagePublic($response->getRender()), $fieldName);
                    }
                }
            }

            if ($isEmpty) {
                if ($required) {
                    $response->addError($response->getRender()->getTranslation('Required'), $fieldName);
                } else {
                    $formData[$fieldName] = null;
                }
            }
        }
        if (!$response->hasErrors()) {
            $action->checkData($formData, $response, $this);
        }

        if ($response->hasErrors()) {
            return null;
        }
        return $action->process($formData, $response, $this);
    }
}
