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
}
