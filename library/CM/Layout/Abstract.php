<?php

class CM_Layout_Abstract extends CM_Component_Abstract {

    public function prepare(CM_Frontend_Environment $environment, CM_Frontend_ViewResponse $viewResponse) {
        /** @var CM_Page_Abstract $page */
        $page = $this->_params->has('page') ? $this->_params->getObject('page', CM_Page_Abstract::class) : null;

        $viewResponse->set('page', $page);
    }

    public function checkAccessible(CM_Frontend_Environment $environment) {
    }

}
