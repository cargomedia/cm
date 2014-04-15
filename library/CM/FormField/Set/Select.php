<?php

class CM_FormField_Set_Select extends CM_FormField_Set {

    const DISPLAY_SELECT = 'select';
    const DISPLAY_RADIOS = 'radios';

    public function validate($userInput, CM_Response_Abstract $response) {
        if (!in_array($userInput, $this->_getValues())) {
            throw new CM_Exception_FormFieldValidation('Invalid value');
        }
        return $userInput;
    }

    public function prepare(CM_Params $renderParams, CM_ViewResponse $viewResponse) {
        $display = $renderParams->getString('display', self::DISPLAY_SELECT);
        if (!in_array($display, array(self::DISPLAY_SELECT, self::DISPLAY_RADIOS), true)) {
            throw new CM_Exception_InvalidParam('Display needs to be either `select` or `radios`');
        }
        $viewResponse->set('display', $display);
        $viewResponse->set('class', $renderParams->getString('class', ''));

        $viewResponse->set('placeholder', $renderParams->getBoolean('placeholder', false));
        $viewResponse->set('optionList', $this->_getOptionList());
        if ($renderParams->has('labelPrefix')) {
            $viewResponse->set('labelPrefix', $renderParams->getString('labelPrefix'));
        }

        $viewResponse->set('translate', $renderParams->getBoolean('translate', false) || $renderParams->has('translatePrefix'));
        $viewResponse->set('translatePrefix', $renderParams->getString('translatePrefix', ''));
    }
}
