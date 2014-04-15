<?php

class CM_FormField_Set extends CM_FormField_Abstract {

    /** @var array */
    private $_values = array();

    /** @var bool */
    private $_labelsInValues = false;

    public function validate($userInput, CM_Response_Abstract $response) {
        foreach ($userInput as $key => $value) {
            if (!in_array($value, $this->_getValues())) {
                unset($userInput[$key]);
            }
        }
        return $userInput;
    }

    public function prepare(CM_Params $renderParams, CM_ViewResponse $viewResponse) {
        $viewResponse->set('class', $renderParams->has('class') ? $renderParams->getString('class') : null);
        $viewResponse->set('optionList', $this->_getOptionList());
        $viewResponse->set('translate', $renderParams->getBoolean('translate', false) || $renderParams->has('translatePrefix'));
        $viewResponse->set('translatePrefix', $renderParams->has('translatePrefix') ? $renderParams->getString('translatePrefix') : null);
    }

    /**
     * @return array
     */
    protected function _getOptionList() {
        if ($this->_labelsInValues || !$this->_values) {
            return $this->_values;
        } else {
            return array_combine($this->_values, $this->_values);
        }
    }

    /**
     * @return array
     */
    protected function _getValues() {
        return array_keys($this->_getOptionList());
    }

    protected function _setup() {
        $this->_values = $this->_params->getArray('values', array());
        $this->_labelsInValues = $this->_params->getBoolean('labelsInValues', false);
    }
}
