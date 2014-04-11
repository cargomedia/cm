<?php

class CM_RenderAdapter_Page extends CM_RenderAdapter_Component {

    /**
     * @param CM_Params $pageParams
     * @return string
     */
    public function fetchDescription(CM_Params $pageParams) {
        return $this->_fetchTemplate('meta-description', $pageParams);
    }

    /**
     * @param CM_Params $pageParams
     * @return string
     */
    public function fetchKeywords(CM_Params $pageParams) {
        return $this->_fetchTemplate('meta-keywords', $pageParams);
    }

    /**
     * @internal param \CM_Params $pageParams
     * @return string
     */
    public function fetchTitle() {
        $viewResponse = new CM_ViewResponse($this->_getView());
        $viewResponse->setTemplateName('title');
        $viewResponse->setData($this->_getView()->getParams()->getAll());
        return trim($this->getRender()->renderViewResponse($viewResponse));
    }

    protected function _getPreparedViewResponse(CM_Component_Abstract $component, CM_RenderEnvironment $environment) {
        $viewResponse = parent::_getPreparedViewResponse($component, $environment);
        $viewResponse->addData('pageTitle', $this->fetchTitle());
        return $viewResponse;
    }

    protected function _getStackKey() {
        return 'pages';
    }

    /**
     * @param string    $templateName
     * @param CM_Params $pageParams
     * @return string
     */
    private function _fetchTemplate($templateName, CM_Params $pageParams) {
        $viewResponse = new CM_ViewResponse($this->_getView());
        $viewResponse->setTemplateName($templateName);
        $viewResponse->setData($pageParams->getAll());
        return trim($this->getRender()->renderViewResponse($viewResponse));
    }
}
