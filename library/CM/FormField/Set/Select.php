<?php

class CM_FormField_Set_Select extends CM_FormField_Set {

    const DISPLAY_SELECT = 'select';
    const DISPLAY_RADIOS = 'radios';

    public function validate(CM_Frontend_Environment $environment, $userInput) {
        if (!in_array($userInput, $this->getValues())) {
            throw new CM_Exception_FormFieldValidation(new CM_I18n_Phrase('Invalid value'));
        }
        return $userInput;
    }

    public function prepare(CM_Params $renderParams, CM_Frontend_Environment $environment, CM_Frontend_ViewResponse $viewResponse) {
        parent::prepare($renderParams, $environment, $viewResponse);

        $display = $renderParams->getString('display', self::DISPLAY_SELECT);
        if (!in_array($display, array(self::DISPLAY_SELECT, self::DISPLAY_RADIOS))) {
            throw new CM_Exception_InvalidParam('Display needs to be either `select` or `radios`');
        }
        $viewResponse->addCssClass($display);
        $viewResponse->set('display', $display);
        $viewResponse->set('labelPrefix', $renderParams->has('labelPrefix') ? $renderParams->getString('labelPrefix') : null);
        $viewResponse->set('placeholder', $renderParams->getBoolean('placeholder', false));
    }
}
