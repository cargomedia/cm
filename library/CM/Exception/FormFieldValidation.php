<?php

class CM_Exception_FormFieldValidation extends CM_Exception {

    /**
     * @param string     $messagePublic
     * @param array|null $variables
     */
    public function __construct($messagePublic, array $variables = null) {
        $messagePublic = (string) $messagePublic;
        parent::__construct('FormField Validation failed', new CM_I18n_Phrase($messagePublic, $variables));
    }
}
