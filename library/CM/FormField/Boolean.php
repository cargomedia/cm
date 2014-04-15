<?php

class CM_FormField_Boolean extends CM_FormField_Abstract {

    public function validate($userInput, CM_Response_Abstract $response) {
        return (bool) $userInput;
    }

    public function prepare(CM_Params $renderParams) {
        $this->setTplParam('tabindex', isset($renderParams['tabindex']) ? (int) $renderParams['tabindex'] : null);
        $this->setTplParam('class', isset($renderParams['class']) ? $renderParams['class'] : null);
        $this->setTplParam('checked', $this->getValue() ? 'checked' : null);
        $this->setTplParam('text', isset($renderParams['text']) ? $renderParams['text'] : null);
    }

    protected function _setup() {
    }
}
