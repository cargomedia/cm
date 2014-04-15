<?php

class CM_FormField_Boolean extends CM_FormField_Abstract {

    public function validate($userInput, CM_Response_Abstract $response) {
        return (bool) $userInput;
    }

    public function prepare(CM_Params $renderParams, CM_ViewResponse $viewResponse) {
        $viewResponse->set('tabindex', $renderParams->has('tabindex') ? $renderParams->getString('tabindex') : null);
        $viewResponse->set('class', $renderParams->has('class') ? $renderParams->getString('class') : null);
        $viewResponse->set('checked', $this->getValue() ? 'checked' : null);
        $viewResponse->set('text', $renderParams->has('text') ? $renderParams->getString('text') : null);
    }

    protected function _setup() {
    }
}
