<?php

class CM_FormField_Url extends CM_FormField_Text {

    public function validate(CM_Frontend_Environment $environment, $userInput) {
        parent::validate($environment, $userInput);

        if (false === filter_var($userInput, FILTER_VALIDATE_URL)) {
            throw new CM_Exception_FormFieldValidation('Invalid url');
        }
    }
}
