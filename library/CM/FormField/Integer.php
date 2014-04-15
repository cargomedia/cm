<?php

class CM_FormField_Integer extends CM_FormField_Abstract {

    public function prepare(CM_Params $renderParams, CM_ViewResponse $viewResponse) {
        $viewResponse->set('class', $renderParams->has('class') ? $renderParams->getString('class') : null);
    }

    public function validate($userInput, CM_Response_Abstract $response) {
        if (!is_numeric($userInput)) {
            throw new CM_Exception_FormFieldValidation('Invalid number');
        }
        $value = (int) $userInput;
        if ($value < $this->_options['min'] || $value > $this->_options['max']) {
            throw new CM_Exception_FormFieldValidation('Value not in range.');
        }
        return $value;
    }

    protected function _setup() {
        $this->_options['min'] = $this->_params->getInt('min', 0);
        $this->_options['max'] = $this->_params->getInt('max', 100);
        $this->_options['step'] = $this->_params->getInt('step', 1);
    }
}
