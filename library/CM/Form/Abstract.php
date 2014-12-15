<?php

abstract class CM_Form_Abstract extends CM_View_Abstract {

    /** @var string */
    private $_name;

    /** @var array */
    private $_fields = array();

    /** @var CM_FormAction_Abstract[] */
    private $_actions = array();

    abstract protected function _initialize();

    public function __construct($params = null) {
        parent::__construct($params);

        $className = get_class($this);
        if (!preg_match('/^\w+_Form_(.+)$/', $className, $matches)) {
            throw new CM_Exception("Cannot detect namespace from form's class-name: `{$className}`");
        }
        $name = lcfirst($matches[1]);
        $name = preg_replace('/([A-Z])/', '_\1', $name);
        $name = strtolower($name);
        $this->_name = $name;
        $this->_initialize();
    }

    public function prepare(CM_Frontend_Environment $environment) {
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
     * @param CM_Http_Response_View_Form $response
     * @return mixed
     */
    public function process(array $data, $actionName, CM_Http_Response_View_Form $response) {
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
                        $formData[$fieldName] = $field->validate($environment, $fieldValue);
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

    /**
     * @param array                   $userInputList
     * @param CM_Frontend_Environment $environment
     * @return array
     */
    protected function _validateValues(array $userInputList, CM_Frontend_Environment $environment) {
        $validValues = array();
        foreach ($userInputList as $name => $userInput) {
            $field = $this->getField($name);
            try {
                $validValues[$name] = $field->validate($environment, $userInput);
            } catch (CM_Exception_FormFieldValidation $e) {
            }
        }
        return $validValues;
    }

    /**
     * @param array $values
     */
    protected function _setValues(array $values) {
        foreach ($values as $name => $value) {
            $this->getField($name)->setValue($value);
        }
    }
}
