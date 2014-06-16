<?php

class CM_FormField_Date extends CM_FormField_Abstract {

    /** @var int */
    protected $_yearFirst;

    /** @var int */
    protected $_yearLast;

    protected function _initialize() {
        $this->_yearFirst = $this->_params->getInt('yearFirst', date('Y') - 100);
        $this->_yearLast = $this->_params->getInt('yearLast', date('Y'));
        parent::_initialize();
    }

    public function parseUserInput($userInput) {
        try {
            $dd = (int) $userInput['day'];
            $mm = (int) $userInput['month'];
            $yy = (int) $userInput['year'];

            return new DateTime($yy . '-' . $mm . '-' . $dd);
        } catch (Exception $e) {
            throw new CM_Exception_FormFieldValidation('Invalid date.');
        }
    }

    /**
     * @param CM_Frontend_Environment $environment
     * @param DateTime                $userInput
     * @throws CM_Exception_FormFieldValidation
     */
    public function validate(CM_Frontend_Environment $environment, $userInput) {
        if (!($userInput instanceof DateTime)) {
            throw new CM_Exception_FormFieldValidation('Expected a DateTime instance.');
        }

        if ($userInput->format('Y') < $this->_yearFirst) {
            throw new CM_Exception_FormFieldValidation('Year should be at least ' . $this->_yearFirst);
        }

        if ($userInput->format('Y') > $this->_yearLast) {
            throw new CM_Exception_FormFieldValidation('Year should be not more than ' . $this->_yearLast);
        }
    }

    public function prepare(CM_Params $renderParams, CM_Frontend_ViewResponse $viewResponse) {
        $viewResponse->set('class', $renderParams->has('class') ? $renderParams->getString('class') : null);

        $years = range($this->_yearFirst, $this->_yearLast);
        $months = range(1, 12);
        $days = range(1, 31);

        $viewResponse->set('years', array_combine($years, $years));
        $viewResponse->set('months', array_combine($months, $months));
        $viewResponse->set('days', array_combine($days, $days));

        $value = $this->getValue();
        $viewResponse->set('yy', $value ? $value->format('Y') : null);
        $viewResponse->set('mm', $value ? $value->format('n') : null);
        $viewResponse->set('dd', $value ? $value->format('j') : null);
    }

    public function isEmpty($userInput) {
        return empty($userInput['day']) || empty($userInput['month']) || empty($userInput['year']);
    }
}
