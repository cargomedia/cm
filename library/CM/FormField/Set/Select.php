<?php

class CM_FormField_Set_Select extends CM_FormField_Set {

    const DISPLAY_SELECT = 'select';
    const DISPLAY_RADIOS = 'radios';

    /** @var bool */
    private $_placeholder;

    public function validate(CM_Frontend_Environment $environment, $userInput) {
        if (!in_array($userInput, $this->_getValues())) {
            throw new CM_Exception_FormFieldValidation('Invalid value');
        }
        return $userInput;
    }

    public function prepare(CM_Params $renderParams, CM_Frontend_ViewResponse $viewResponse) {
        $display = $renderParams->getString('display', self::DISPLAY_SELECT);
        if (!in_array($display, array(self::DISPLAY_SELECT, self::DISPLAY_RADIOS))) {
            throw new CM_Exception_InvalidParam('Display needs to be either `select` or `radios`');
        }
        $viewResponse->addCssClass($display);
        $viewResponse->set('display', $display);

        $viewResponse->set('class', $renderParams->has('class') ? $renderParams->getString('class') : null);
        $viewResponse->set('placeholder', $renderParams->getBoolean('placeholder', $this->_placeholder));
        $viewResponse->set('optionList', $this->_getOptionList());
        $viewResponse->set('labelPrefix', $renderParams->has('labelPrefix') ? $renderParams->getString('labelPrefix') : null);

        $viewResponse->set('translate', $renderParams->getBoolean('translate', false) || $renderParams->has('translatePrefix'));
        $viewResponse->set('translatePrefix', $renderParams->getString('translatePrefix', ''));
    }

    protected function _initialize() {
        $this->_placeholder = $this->_params->getBoolean('placeholder', false);
        parent::_initialize();
    }
}
