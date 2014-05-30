<?php

class CM_FormField_Site extends CM_FormField_Set_Select {

    public function initialize() {
        $valuesSet = array();
        foreach (CM_Site_Abstract::getAll() as $site) {
            $valuesSet[$site->getType()] = $site->getName();
        }
        $this->_params->set('values', $valuesSet);
        $this->_params->set('labelsInValues', true);
        parent::initialize();
    }

    /**
     * @param int                  $userInput
     * @param CM_Response_Abstract $response
     * @return CM_Site_Abstract
     */
    public function validate($userInput, CM_Response_Abstract $response) {
        $userInput = parent::validate($userInput, $response);
        return CM_Site_Abstract::factory($userInput);
    }
}
