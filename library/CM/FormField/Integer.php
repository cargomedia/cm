<?php

class CM_FormField_Integer extends CM_FormField_Float {
    
    public function validate(CM_Frontend_Environment $environment, $userInput) {
        $value = parent::validate($environment, $userInput);
        if ((int) $value != $value) {
            throw new CM_Exception_FormFieldValidation(new CM_I18n_Phrase('Invalid integer'));
        }
        return $value;
    }
}
