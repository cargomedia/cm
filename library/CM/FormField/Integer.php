<?php

class CM_FormField_Integer extends CM_FormField_Float {

    const DISPLAY_TEXT = 'default';
    const DISPLAY_SLIDER = 'slider';
    const DISPLAY_DEFAULT = self::DISPLAY_TEXT;

    public function validate(CM_Frontend_Environment $environment, $userInput) {
        $userInput = parent::validate($environment, $userInput);
        if ((int) $userInput != $userInput) {
            throw new CM_Exception_FormFieldValidation(new CM_I18n_Phrase('Invalid integer'));
        }
        $value = (int) $userInput;
        if ($value < $this->_options['min'] || $value > $this->_options['max']) {
            throw new CM_Exception_FormFieldValidation(new CM_I18n_Phrase('Value not in range.'));
        }
        return $value;
    }

    protected function _initialize() {
        $this->_options['min'] = $this->_params->getInt('min', 0);
        $this->_options['max'] = $this->_params->getInt('max', 100);
        parent::_initialize();
    }

    public function prepare(CM_Params $renderParams, CM_Frontend_Environment $environment, CM_Frontend_ViewResponse $viewResponse) {
        parent::prepare($renderParams, $environment, $viewResponse);

        $display = $renderParams->getString('display', static::DISPLAY_DEFAULT);
        if (!in_array($display, [self::DISPLAY_TEXT, self::DISPLAY_SLIDER])) {
            throw new CM_Exception_InvalidParam('Display needs to be either `default` or `slider`');
        }
        $viewResponse->setTemplateName($display);
        $viewResponse->getJs()->setProperty('display', $display);
    }
}
