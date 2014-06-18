<?php

class CM_FormField_Site extends CM_FormField_Set_Select {

    protected function _initialize() {
        $valuesSet = array();
        foreach (CM_Site_Abstract::getAll() as $site) {
            $valuesSet[$site->getType()] = $site->getName();
        }
        $this->_params->set('values', $valuesSet);
        $this->_params->set('labelsInValues', true);
        parent::_initialize();
    }

    public function parseUserInput($userInput) {
        return CM_Site_Abstract::factory($userInput);
    }

    /**
     * @param CM_Frontend_Environment $environment
     * @param CM_Site_Abstract        $userInput
     * @throws CM_Exception_FormFieldValidation
     */
    public function validate(CM_Frontend_Environment $environment, $userInput) {
        parent::validate($environment, $userInput);

        if (!$userInput instanceof CM_Site_Abstract) {
            throw new CM_Exception_FormFieldValidation('Expected a CM_Site_Abstract instance.');
        }
    }
}
