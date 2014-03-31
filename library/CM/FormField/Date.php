<?php

class CM_FormField_Date extends CM_FormField_Abstract {

    /** @var int */
    protected $_yearFirst;

    /** @var int */
    protected $_yearLast;

    /**
     * @param int|null $yearFirst
     * @param int|null $yearLast
     */
    public function __construct($yearFirst = null, $yearLast = null) {
        if (null === $yearFirst) {
            $yearFirst = date('Y') - 100;
        }
        $this->_yearFirst = (int) $yearFirst;

        if (null === $yearLast) {
            $yearLast = date('Y');
        }
        $this->_yearLast = (int) $yearLast;
    }

    public function validate($userInput, CM_Response_Abstract $response) {
        $dd = (int) trim($userInput['day']);
        $mm = (int) trim($userInput['month']);
        $yy = (int) trim($userInput['year']);

        return new DateTime($yy . '-' . $mm . '-' . $dd);
    }

    public function prepare(array $params) {
        $this->setTplParam('class', isset($params['class']) ? $params['class'] : null);

        $years = range($this->_yearFirst, $this->_yearLast);
        $months = range(1, 12);
        $days = range(1, 31);

        $this->setTplParam('years', array_combine($years, $years));
        $this->setTplParam('months', array_combine($months, $months));
        $this->setTplParam('days', array_combine($days, $days));

        $value = $this->getValue();
        $this->setTplParam('yy', $value ? $value->format('Y') : null);
        $this->setTplParam('mm', $value ? $value->format('n') : null);
        $this->setTplParam('dd', $value ? $value->format('j') : null);
    }

    public function isEmpty($userInput) {
        return empty($userInput['day']) || empty($userInput['month']) || empty($userInput['year']);
    }
}
