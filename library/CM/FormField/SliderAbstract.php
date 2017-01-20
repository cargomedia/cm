<?php

abstract class CM_FormField_SliderAbstract extends CM_FormField_Abstract {

    protected function _initialize() {
        parent::_initialize();
        $this->_options['min'] = $this->_params->getFloat('min', 0);
        $this->_options['max'] = $this->_params->getFloat('max', 100);
        $this->_options['step'] = $this->_params->getFloat('step', 1);
        $this->_options['cardinality'] = 1;
    }

    public function prepare(CM_Params $renderParams, CM_Frontend_Environment $environment, CM_Frontend_ViewResponse $viewResponse) {
        $viewResponse->set('class', $renderParams->has('class') ? $renderParams->getString('class') : null);
        $viewResponse->getJs()->setProperty('sliderStart', $this->_getSliderStart());
    }

    /**
     * @param CM_Frontend_Environment $environment
     * @param string                  $userInput
     * @return float[]
     * @throws CM_Exception_FormFieldValidation
     */
    public function validate(CM_Frontend_Environment $environment, $userInput) {
        $userInput = (array) $userInput;

        foreach ($userInput as $item) {
            if (!is_numeric($item)) {
                throw new CM_Exception_FormFieldValidation(new CM_I18n_Phrase('Invalid value'));
            }
            if ($item < $this->_options['min'] || $item > $this->_options['max']) {
                throw new CM_Exception_FormFieldValidation(new CM_I18n_Phrase('Value not in range'));
            }
        }

        sort($userInput);
        return $userInput;
    }

    /**
     * @return mixed
     */
    public function getValue() {
        $value = parent::getValue();
        if (null === $value) {
            $value = $this->_getDefaultValue();
        }
        return $value;
    }

    /**
     * @return mixed
     */
    abstract protected function _getDefaultValue();

    /**
     * @return float[]
     */
    abstract protected function _getSliderStart();

}
