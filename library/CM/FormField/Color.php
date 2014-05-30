<?php

class CM_FormField_Color extends CM_FormField_Abstract {

    public function validate(CM_Frontend_Environment $environment, $userInput) {
        if (!preg_match('/^#[abcdef\d]{6}$/i', $userInput)) {
            throw new CM_Exception_FormFieldValidation('Invalid color');
        }
        return (string) $userInput;
    }
}
