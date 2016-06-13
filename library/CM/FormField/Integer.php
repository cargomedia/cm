<?php

class CM_FormField_Integer extends CM_FormField_Text {

    public function validate(CM_Frontend_Environment $environment, $userInput) {
        if (!is_numeric($userInput)) {
            throw new CM_Exception_FormFieldValidation(new CM_I18n_Phrase('Invalid number'));
        }
   
        return (int) $userInput;
    }
}
