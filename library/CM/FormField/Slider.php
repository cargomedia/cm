<?php

class CM_FormField_Slider extends CM_FormField_SliderAbstract {

    protected function _initialize() {
        parent::_initialize();
        $this->_options['cardinality'] = 1;
    }

    /**
     * @param CM_Frontend_Environment $environment
     * @param string                  $userInput
     * @return float
     */
    public function validate(CM_Frontend_Environment $environment, $userInput) {
        $userInput = parent::validate($environment, $userInput);
        return Functional\first($userInput);
    }

    /**
     * @return float
     */
    protected function _getDefaultValue() {
        return $this->_options['min'];
    }

    /**
     * @return float[]
     */
    protected function _getSliderStart() {
        return [$this->getValue()];
    }
}
