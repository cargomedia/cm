<?php

class CM_FormField_Boolean extends CM_FormField_Abstract {

    public function validate($userInput, CM_Response_Abstract $response) {
        return (bool) $userInput;
    }

    public function prepare(CM_Params $renderParams, CM_ViewResponse $viewResponse) {
        $this->setTplParam('tabindex', $renderParams->getString('tabindex', ''));
        $this->setTplParam('class', $renderParams->getString('class', ''));
        $this->setTplParam('checked', $this->getValue() ? 'checked' : null);
        $this->setTplParam('text', $renderParams->getString('text', ''));
    }

    protected function _setup() {
    }
}
