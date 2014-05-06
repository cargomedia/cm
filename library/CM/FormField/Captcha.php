<?php

class CM_FormField_Captcha extends CM_FormField_Abstract {

    public function prepare(CM_Params $renderParams, CM_ViewResponse $viewResponse) {
        $viewResponse->set('imageId', CM_Captcha::create()->getId());
    }

    public function validate($userInput, CM_Response_Abstract $response) {
        $id = (int) $userInput['id'];
        $text = (string) $userInput['value'];

        try {
            $captcha = new CM_Captcha($id);
        } catch (CM_Exception_Nonexistent $e) {
            throw new CM_Exception_FormFieldValidation('Invalid captcha reference');
        }
        if (!$captcha->check($text)) {
            throw new CM_Exception_FormFieldValidation('Number doesn\'t match captcha');
        }

        return $userInput;
    }

    public function ajax_createNumber(CM_Params $params, CM_Frontend_JavascriptContainer_View $handler, CM_Response_View_Ajax $response) {
        return CM_Captcha::create()->getId();
    }

    protected function _setup() {
    }
}
