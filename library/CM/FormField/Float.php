<?php

class CM_FormField_Float extends CM_FormField_Text {

    public function validate(CM_Frontend_Environment $environment, $userInput) {
        $userInput = parent::validate($environment, $userInput);
        if (!is_numeric($userInput)) {
            throw new CM_Exception_FormFieldValidation(new CM_I18n_Phrase('Not numeric'));
        }
        $userInput = (float) $userInput;

        if (isset($this->_options['min']) && $userInput < $this->_options['min']) {
            throw new CM_Exception_FormFieldValidation(new CM_I18n_Phrase('Value cannot be lesser than ${minimum}.', ['minimum' => $this->_options['min']]));
        }

        if (isset($this->_options['max']) && $userInput > $this->_options['max']) {
            throw new CM_Exception_FormFieldValidation(new CM_I18n_Phrase('Value cannot be greater than ${maximum}', ['maximum' => $this->_options['max']]));
        }
        return $userInput;
    }

    protected function _initialize() {
        $this->_setMinMaxOptions();
        parent::_initialize();
    }

    protected function _setMinMaxOptions() {
        if ($this->_params->has('min')) {
            $this->_options['min'] = $this->_params->getFloat('min');
        }
        if ($this->_params->has('max')) {
            $this->_options['max'] = $this->_params->getFloat('max');
        }
    }
}
