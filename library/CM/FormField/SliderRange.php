<?php

class CM_FormField_SliderRange extends CM_FormField_SliderAbstract {

    protected function _initialize() {
        parent::_initialize();
        $this->_options['cardinality'] = 2;
    }

    /**
     * @param CM_Frontend_Environment $environment
     * @param string                  $userInput
     * @return float[]
     */
    public function validate(CM_Frontend_Environment $environment, $userInput) {
        return parent::validate($environment, $userInput);
    }

    /**
     * @return float[]
     */
    protected function _getDefaultValue() {
        return [$this->_options['min'], $this->_options['max']];
    }

    /**
     * @return float[]
     */
    protected function _getSliderStart() {
        return $this->getValue();
    }

}
