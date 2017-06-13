<?php

class CM_FormField_Site extends CM_FormField_Set_Select {

    protected function _initialize() {
        $valuesSet = array();
        foreach ((new CM_Paging_Site_All())->getItems() as $site) {
            /** @var CM_Site_Abstract $site */
            $valuesSet[$site->getId()] = $site->getUrl();
        }
        $this->_params->set('values', $valuesSet);
        $this->_params->set('labelsInValues', true);
        parent::_initialize();
    }

    /**
     * @param CM_Frontend_Environment $environment
     * @param int                     $userInput
     * @return CM_Site_Abstract
     */
    public function validate(CM_Frontend_Environment $environment, $userInput) {
        $userInput = parent::validate($environment, $userInput);
        return (new CM_Site_SiteFactory())->getSiteById($userInput);
    }
}
