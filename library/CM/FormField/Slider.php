<?php

class CM_FormField_Slider extends CM_FormField_Abstract {

    public function prepare(CM_Params $renderParams, CM_Frontend_Environment $environment, CM_Frontend_ViewResponse $viewResponse) {
        $viewResponse->set('class', $renderParams->has('class') ? $renderParams->getString('class') : null);
        $viewResponse->getJs()->setProperty('sliderStart', $this->_getSliderStart());
    }

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

        if (1 === $this->_getCardinality()) {
            $userInput = Functional\first($userInput);
        }
        return $userInput;
    }

    protected function _initialize() {
        $this->_options['min'] = $this->_params->getFloat('min', 0);
        $this->_options['max'] = $this->_params->getFloat('max', 100);
        $this->_options['step'] = $this->_params->getFloat('step', 1);
        $this->_options['cardinality'] = $this->_getCardinality();
        parent::_initialize();
    }

    /**
     * @return int
     */
    protected function _getCardinality() {
        return 1;
    }

    /**
     * @return int[]
     * @throws CM_Exception
     */
    protected function _getSliderStart() {
        $sliderValue = $this->getValue();
        if (null === $sliderValue) {
            $sliderValue = array_fill(0, $this->_getCardinality(), $this->_options['min']);
        }
        $sliderValue = (array) $sliderValue;
        if (count($sliderValue) !== $this->_getCardinality()) {
            throw new CM_Exception('Initial slider value doesnt match cardinality', null, [
                'cardinality' => $this->_getCardinality(),
                'valueSize'   => count($sliderValue),
            ]);
        }
        return $sliderValue;
    }
}
