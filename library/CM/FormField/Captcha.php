<?php

class CM_FormField_Captcha extends CM_FormField_Abstract {

    public function prepare(CM_Params $renderParams, CM_Frontend_ViewResponse $viewResponse) {
        $viewResponse->set('imageId', CM_Captcha::create()->getId());
    }

    public function parseUserInput($userInput) {
        if (!isset($userInput['id']) || !isset($userInput['value'])) {
            throw new CM_Exception_FormFieldValidation('Expected id and value.');
        }

        return array(
            'id' => (int) $userInput['id'],
            'value' => (string) $userInput['value']
        );
    }

    public function validate(CM_Frontend_Environment $environment, $userInput) {
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
    }

    public function ajax_createNumber(CM_Params $params, CM_Frontend_JavascriptContainer_View $handler, CM_Response_View_Ajax $response) {
        return CM_Captcha::create()->getId();
    }
}
