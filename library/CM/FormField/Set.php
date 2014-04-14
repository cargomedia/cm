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

    public function prepare(array $params) {
        $this->setTplParam('class', !empty($params['class']) ? $params['class'] : null);
        $this->setTplParam('optionList', $this->_getOptionList());
        $this->setTplParam('translate', !empty($params['translate']) || !empty($params['translatePrefix']));
        $this->setTplParam('translatePrefix', !empty($params['translatePrefix']) ? $params['translatePrefix'] : '');
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

    /**
     * @param array|null $values
     * @param bool|null  $labelsInValues
     * @return CM_FormField_Set
     */
    public static function create(array $values = null, $labelsInValues = null) {
        return new static(array(
            'values' => $values,
            'labelsInValues' => $labelsInValues,
        ));
    }
}
