<?php

class CM_FormField_Float extends CM_FormField_Text {

    public function validate(CM_Frontend_Environment $environment, $userInput, CM_Response_Abstract $response) {
        $userInput = parent::validate($environment, $userInput, $response);
        if (!is_numeric($userInput)) {
            throw new CM_Exception_FormFieldValidation('Not numeric');
        }
        return (float) $userInput;
    }
}
