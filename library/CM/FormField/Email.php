<?php

class CM_FormField_Email extends CM_FormField_Text {

    public function validate(CM_Frontend_Environment $environment, $userInput) {
        parent::validate($environment, $userInput);

        /** @var CM_Service_EmailVerification_ClientInterface $emailVerification */
        $emailVerification = CM_Service_Manager::getInstance()->get('email-verification', 'CM_Service_EmailVerification_ClientInterface');
        if (!$emailVerification->isValid($userInput)) {
            throw new CM_Exception_FormFieldValidation('Invalid email address');
        }
    }
}
