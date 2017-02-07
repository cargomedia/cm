<?php

class CM_FormField_DateTimeInterval extends CM_FormField_Abstract {

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
        $start = trim($userInput['start']);
        $end = trim($userInput['end']);

        $base = new DateTime($yy . '-' . $mm . '-' . $dd, $this->_getTimeZone($environment));
        $from = clone $base;
        $from->add($this->_processTime($start));
        $until = clone $base;
        $until->add($this->_processTime($end));
        if ($until <= $from) {
            $until->add(new DateInterval('P1D'));
        }
        return [$from, $until];
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
        $viewResponse->set('placeholderStart', $renderParams->has('placeholderStart') ? $renderParams->getString('placeholderStart') : null);
        $viewResponse->set('placeholderEnd', $renderParams->has('placeholderEnd') ? $renderParams->getString('placeholderEnd') : null);
    }

    public function isEmpty($userInput) {
        return empty($userInput['day']) || empty($userInput['month']) || empty($userInput['year']) || empty($userInput['start']) || empty($userInput['end']);
    }

    /**
     * @param string $userInput
     * @return DateInterval
     * @throws CM_Exception_FormFieldValidation
     */
    protected function _processTime($userInput) {
        if (!preg_match('/^(\d{1,2})(?:[:\.](\d{2}))?$/', $userInput, $matches)) {
            throw new CM_Exception_FormFieldValidation(new CM_I18n_Phrase('Invalid Time.'));
        }
        $hour = (int) $matches[1];
        $minute = array_key_exists(2, $matches) ? (int) $matches[2] : 0;
        if ($hour > 24 || $minute > 60 || ($hour === 24 && $minute > 0)) {
            throw new CM_Exception_FormFieldValidation(new CM_I18n_Phrase('Invalid Time'));
        }
        return new DateInterval('PT' . $hour . 'H' . $minute . 'M');
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
