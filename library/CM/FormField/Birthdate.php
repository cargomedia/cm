<?php

class CM_FormField_Birthdate extends CM_FormField_Date {

    /** @var integer */
    protected $_minAge;

    /** @var integer */
    protected $_maxAge;

    protected function _initialize() {
        $this->_minAge = $this->_params->getInt('minAge');
        $this->_maxAge = $this->_params->getInt('maxAge');

        $this->_params->set('yearFirst', date('Y') - $this->_minAge);
        $this->_params->set('yearLast', date('Y') - $this->_maxAge);
        parent::_initialize();
    }

    public function validate(CM_Frontend_Environment $environment, $userInput) {
        $userInput = parent::validate($environment, $userInput);
        $age = $userInput->diff(new DateTime())->y;
        if ($age < $this->_minAge || $age > $this->_maxAge) {
            throw new CM_Exception_FormFieldValidation('Invalid birthdate');
        }
        return $userInput;
    }
}
