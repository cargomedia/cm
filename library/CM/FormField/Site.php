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
     * @param int                     $userInput
     * @return CM_Site_Abstract
     */
    public function validate(CM_Frontend_Environment $environment, $userInput) {
        return parent::validate($environment, $userInput);
    }
}
