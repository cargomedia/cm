<?php

class CM_Exception_FormFieldValidation extends CM_Exception {

    /**
     * @param CM_I18n_Phrase $messagePublic
     */
    public function __construct(CM_I18n_Phrase $messagePublic) {
        parent::__construct('FormField Validation failed', null, null, [
            'messagePublic' => $messagePublic,
        ]);
    }
}
