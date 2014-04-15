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
        $this->setTplParam('class', !empty($renderParams['class']) ? $renderParams['class'] : null);
        $this->setTplParam('optionList', $this->_getOptionList());
        $this->setTplParam('translate', !empty($renderParams['translate']) || !empty($renderParams['translatePrefix']));
        $this->setTplParam('translatePrefix', !empty($renderParams['translatePrefix']) ? $renderParams['translatePrefix'] : '');
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
