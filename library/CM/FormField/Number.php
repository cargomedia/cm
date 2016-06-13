<?php

class CM_FormField_Number extends CM_FormField_Text {

    public function validate(CM_Frontend_Environment $environment, $userInput) {
        if (!is_numeric($userInput)) {
            throw new CM_Exception_FormFieldValidation(new CM_I18n_Phrase('Invalid number'));
        }
        $value = (int) $userInput;
        if ($value < $this->_options['min'] || $value > $this->_options['max']) {
            throw new CM_Exception_FormFieldValidation(new CM_I18n_Phrase('Value not in range.'));
        }

        return $value;
    }

    protected function _initialize() {
        $this->_options['min'] = $this->_params->getInt('min', 0);
        $this->_options['max'] = $this->_params->getInt('max', 100);
        parent::_initialize();
    }
}
