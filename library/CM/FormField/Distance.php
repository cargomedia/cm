<?php

class CM_FormField_Distance extends CM_FormField_Integer {

    public function validate(CM_Frontend_Environment $environment, $userInput) {
        parent::validate($environment, $userInput);
        $value = (int) $userInput;
        if ($value < $this->_options['min'] || $value > $this->_options['max']) {
            throw new CM_Exception_FormFieldValidation(new CM_I18n_Phrase('Value not in range.'));
        }

        return $value * 1609;
    }

    /**
     * @return int External Value
     */
    public function getValue() {
        return parent::getValue() / 1609;
    }

    protected function _initialize() {
        $this->_options['min'] = $this->_params->getInt('min', 0);
        $this->_options['max'] = $this->_params->getInt('max', 100);
        $this->_options['step'] = $this->_params->getInt('step', 1);
        parent::_initialize();
    }
}
