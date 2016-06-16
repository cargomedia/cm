<?php

class CM_FormField_Slider extends CM_FormField_Abstract {

    public function prepare(CM_Params $renderParams, CM_Frontend_Environment $environment, CM_Frontend_ViewResponse $viewResponse) {
        $viewResponse->set('class', $renderParams->has('class') ? $renderParams->getString('class') : null);
    }

    public function validate(CM_Frontend_Environment $environment, $userInput) {
        if (!is_numeric($userInput)) {
            throw new CM_Exception_FormFieldValidation(new CM_I18n_Phrase('Invalid number'));
        }

        $possibleValues = range($this->_options['min'], $this->_options['max'], $this->_options['step']);
        $value = (float) $userInput;
        if (!in_array($value, $possibleValues, true)) {
            throw new CM_Exception_FormFieldValidation(new CM_I18n_Phrase('Value not in range.'));
        }
        return $value;
    }

    protected function _initialize() {
        $this->_options['min'] = $this->_params->getFloat('min', 0);
        $this->_options['max'] = $this->_params->getFloat('max', 100);
        $this->_options['step'] = $this->_params->getFloat('step', 1);
        parent::_initialize();
    }
}
