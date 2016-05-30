<?php

class CM_FormField_Time extends CM_FormField_Text {

    /** @var DateTimeZone|null */
    private $_timeZone;

    protected function _initialize() {
        $this->_timeZone = $this->_params->has('timeZone') ? $this->_params->getDateTimeZone('timeZone') : null;
        parent::_initialize();
    }

    /**
     * @param DateTime|DateInterval $value
     */
    public function setValue($value) {
        if ($value instanceof DateInterval) {
            $value = (new DateTime())->setTime(0, 0, 0)->add($value);
        }
        parent::setValue($value);
    }

    public function validate(CM_Frontend_Environment $environment, $userInput) {
        $userInput = parent::validate($environment, $userInput);

        if (!preg_match('/^(\d{1,2})(?:[:\.](\d{2}))?$/', $userInput, $matches)) {
            throw new CM_Exception_FormFieldValidation(new CM_I18n_Phrase('Illegal time format.'));
        }
        $hour = (int) $matches[1];
        $minute = array_key_exists(2, $matches) ? $matches[2] : 0;
        if ($hour > 24 || $minute > 60 || ($hour === 24 && $minute > 0)) {
            throw new CM_Exception_FormFieldValidation(new CM_I18n_Phrase('Illegal time format.'));
        }
        return new DateInterval('PT' . $hour . 'H' . $minute . 'M');
    }

    public function prepare(CM_Params $renderParams, CM_Frontend_Environment $environment, CM_Frontend_ViewResponse $viewResponse) {
        parent::prepare($renderParams, $environment, $viewResponse);

        $viewResponse->set('timeZone', $this->_getTimeZone($environment));
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
