<?php

class CM_FormField_Integer extends CM_FormField_Abstract {

    public function prepare(CM_Params $renderParams, CM_Frontend_ViewResponse $viewResponse) {
        $viewResponse->set('class', $renderParams->has('class') ? $renderParams->getString('class') : null);
    }

    public function validate(CM_Frontend_Environment $environment, $userInput) {
        if (!is_numeric($userInput)) {
            throw new CM_Exception_FormFieldValidation('Invalid number');
        }
        $value = (int) $userInput;
        if ($value < $this->_options['min'] || $value > $this->_options['max']) {
            throw new CM_Exception_FormFieldValidation('Value not in range.');
        }
        return $value;
    }

    public function initialize() {
        $this->_options['min'] = $this->_params->getInt('min', 0);
        $this->_options['max'] = $this->_params->getInt('max', 100);
        $this->_options['step'] = $this->_params->getInt('step', 1);
        parent::initialize();
    }
}
