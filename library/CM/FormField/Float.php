<?php

class CM_FormField_Float extends CM_FormField_Text {

    public function parseUserInput($userInput) {
        return (float)$userInput;
    }

    public function validate(CM_Frontend_Environment $environment, $userInput) {
        if (!is_numeric($userInput)) {
            throw new CM_Exception_FormFieldValidation('Not numeric');
        }
    }
}
