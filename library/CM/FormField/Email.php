<?php

class CM_FormField_Email extends CM_FormField_Text {

    public function validate(CM_Frontend_Environment $environment, $userInput) {
        $userInput = parent::validate($environment, $userInput);

        $emailVerification = $this->_getEmailVerification();
        if (!$emailVerification->isValid($userInput)) {
            throw new CM_Exception_FormFieldValidation('Invalid email address');
        }

        return $userInput;
    }

    /**
     * @return CM_Service_EmailVerification_ClientInterface
     */
    protected function _getEmailVerification() {
        $emailVerificationDefault = CM_Service_Manager::getInstance()->get('email-verification', 'CM_Service_EmailVerification_ClientInterface');
        return $this->getParams()->getObject('email-verification', 'CM_Service_EmailVerification_ClientInterface', $emailVerificationDefault);
    }
}
