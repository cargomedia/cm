<?php

abstract class CM_FormField_SuggestOne extends CM_FormField_Suggest {

    public function initialize() {
        $this->_params->set('cardinality', 1);
        parent::initialize();
    }

    public function setValue($value) {
        $value = $value ? array($value) : null;
        parent::setValue($value);
    }

    /**
     * @param string               $userInput
     * @param CM_Response_Abstract $response
     * @return string|null
     */
    public function validate($userInput, CM_Response_Abstract $response) {
        $values = parent::validate($userInput, $response);
        return $values ? reset($values) : null;
    }
}
