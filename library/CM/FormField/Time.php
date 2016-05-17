<?php

class CM_FormField_Time extends CM_FormField_Text {

    public function validate(CM_Frontend_Environment $environment, $userInput) {
        $userInput = parent::validate($environment, $userInput);

        if (!preg_match('/^(\d{1,2})(?:[:\.](\d{2}))?$/', $userInput, $matches)) {
            throw new CM_Exception_FormFieldValidation(new CM_I18n_Phrase('Invalid time.'));
        }
        $hour = (int) $matches[1];
        $minute = array_key_exists(2, $matches) ? $matches[2] : 0;
        if ($hour > 23 || $minute > 59) {
            throw new CM_Exception_FormFieldValidation(new CM_I18n_Phrase('Invalid time.'));
        }
        return new DateInterval('PT' . $hour . 'H' . $minute . 'M');
    }
}
