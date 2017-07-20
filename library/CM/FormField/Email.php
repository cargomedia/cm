<?php

class CM_FormField_Email extends CM_FormField_Text {

    public function validate(CM_Frontend_Environment $environment, $userInput) {
        $userInput = parent::validate($environment, $userInput);

        $emailVerification = $this->_getEmailVerification();
        if (!$emailVerification->isValid($userInput)) {
            throw new CM_Exception_FormFieldValidation(new CM_I18n_Phrase('Invalid email address'));
        }

        return $userInput;
    }

    /**
     * @return CM_Service_EmailVerification_ClientInterface
     */
    protected function _getEmailVerification() {
        if (!$this->getParams()->getBoolean('enable-email-verification', false)) {
            return new CM_Service_EmailVerification_Standard();
        }
        return CM_Service_Manager::getInstance()->get('email-verification', 'CM_Service_EmailVerification_ClientInterface');
    }
}
