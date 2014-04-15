<?php

class CM_FormField_Boolean extends CM_FormField_Abstract {

    public function validate($userInput, CM_Response_Abstract $response) {
        return (bool) $userInput;
    }

    public function prepare(CM_Params $renderParams, CM_ViewResponse $viewResponse) {
        $viewResponse->set('tabindex', $renderParams->getString('tabindex', ''));
        $viewResponse->set('class', $renderParams->getString('class', ''));
        $viewResponse->set('checked', $this->getValue() ? 'checked' : null);
        $viewResponse->set('text', $renderParams->getString('text', ''));
    }

    protected function _setup() {
    }
}
