<?php

abstract class CM_FormField_SuggestOne extends CM_FormField_Suggest {

    protected function _initialize() {
        $this->_params->set('cardinality', 1);
        parent::_initialize();
    }

    public function setValue($value) {
        $value = $value ? array($value) : null;
        parent::setValue($value);
    }

    public function parseUserInput($userInput) {
        $values = parent::parseUserInput($userInput);
        if (count($values) > 1) {
            throw new CM_Exception_FormFieldValidation('Too many elements.');
        }
        return $values[0];
    }

    /**
     * @param CM_Frontend_Environment $environment
     * @param string                  $userInput
     * @return string|null
     */
    public function validate(CM_Frontend_Environment $environment, $userInput) {
    }
}
