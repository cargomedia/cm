<?php

class CM_FormField_Integer extends CM_FormField_Float {
    
    public function validate(CM_Frontend_Environment $environment, $userInput) {
        $userInput = parent::validate($environment, $userInput);
        $value = (int) $userInput;
        if ($value != $userInput) {
            throw new CM_Exception_FormFieldValidation(new CM_I18n_Phrase('Invalid integer'));
        }
        return $value;
    }

    protected function _setMinMaxOptions() {
        if ($this->_params->has('min')) {
            $this->_options['min'] =  $this->_params->getInt('min');
        }
        if ($this->_params->has('max')) {
            $this->_options['max'] =  $this->_params->getInt('max');
        }
    }
}
