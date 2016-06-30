<?php

class CM_FormField_Date extends CM_FormField_Abstract {

    /** @var DateTimeZone|null */
    protected $_timeZone;

    /** @var int */
    protected $_yearFirst;

    /** @var int */
    protected $_yearLast;

    protected function _initialize() {
        $this->_timeZone = $this->_params->has('timeZone') ? $this->_params->getDateTimeZone('timeZone') : null;
        $this->_yearFirst = $this->_params->getInt('yearFirst', date('Y') - 100);
        $this->_yearLast = $this->_params->getInt('yearLast', date('Y'));
        parent::_initialize();
    }

    public function validate(CM_Frontend_Environment $environment, $userInput) {
        $dd = (int) trim($userInput['day']);
        $mm = (int) trim($userInput['month']);
        $yy = (int) trim($userInput['year']);

        return new DateTime($yy . '-' . $mm . '-' . $dd, $this->_getTimeZone($environment));
    }

    public function prepare(CM_Params $renderParams, CM_Frontend_Environment $environment, CM_Frontend_ViewResponse $viewResponse) {
        $viewResponse->set('class', $renderParams->has('class') ? $renderParams->getString('class') : null);

        $years = range($this->_yearFirst, $this->_yearLast);
        $months = range(1, 12);
        $days = range(1, 31);

        $viewResponse->set('years', array_combine($years, $years));
        $viewResponse->set('months', array_combine($months, $months));
        $viewResponse->set('days', array_combine($days, $days));

        /** @var DateTime|null $value */
        $value = $this->getValue();
        $year = $month = $day = null;
        if (null !== $value) {
            $value->setTimezone($this->_getTimeZone($environment));
            $year = $value->format('Y');
            $month = $value->format('n');
            $day = $value->format('j');
        }

        $viewResponse->set('yy', $year);
        $viewResponse->set('mm', $month);
        $viewResponse->set('dd', $day);
    }

    public function isEmpty($userInput) {
        return empty($userInput['day']) || empty($userInput['month']) || empty($userInput['year']);
    }

    /**
     * @param CM_Frontend_Environment $environment
     * @return DateTimeZone
     */
    protected function _getTimeZone(CM_Frontend_Environment $environment) {
        if (null === $this->_timeZone) {
            return $environment->getTimeZone();
        }
        return $this->_timeZone;
    }
}
