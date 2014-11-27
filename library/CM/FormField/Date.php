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

    public function validate(CM_Frontend_Environment $environment, $userInput) {
        if (isset($userInput['date'])) {
            return new DateTime(trim($userInput['date']));
        } else {
            $dd = (int) trim($userInput['day']);
            $mm = (int) trim($userInput['month']);
            $yy = (int) trim($userInput['year']);
            return new DateTime(self::format($yy, $mm, $dd));
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
        $yy = $value ? $value->format('Y') : null;
        $mm = $value ? $value->format('n') : null;
        $dd = $value ? $value->format('j') : null;

        $viewResponse->set('yy', $yy);
        $viewResponse->set('mm', $mm);
        $viewResponse->set('dd', $dd);
        $viewResponse->set('date', self::format($yy, $mm, $dd));
    }

    public function isEmpty($userInput) {
        return (empty($userInput['day']) || empty($userInput['month']) || empty($userInput['year'])) && (empty($userInput['date']));
    }

    public static function format($yy, $mm, $dd) {
        return $yy . '-' . $mm . '-' . $dd;
    }
}
