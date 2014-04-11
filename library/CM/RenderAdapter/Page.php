<?php

class CM_RenderAdapter_Page extends CM_RenderAdapter_Component {

    /**
     * @param CM_Page_Abstract $page
     * @return string
     */
    public function fetchDescription(CM_Page_Abstract $page) {
        return $this->_fetchTemplate($page, 'meta-description');
    }

    /**
     * @param CM_Page_Abstract $page
     * @return string
     */
    public function fetchKeywords(CM_Page_Abstract $page) {
        return $this->_fetchTemplate($page, 'meta-keywords');
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

    protected function _getPreparedViewResponse(CM_Component_Abstract $component, CM_RenderEnvironment $environment, CM_ComponentFrontendHandler $frontendHandler) {
        $viewResponse = parent::_getPreparedViewResponse($component, $environment, $frontendHandler);
        $viewResponse->addData('pageTitle', $this->fetchTitle());
        return $viewResponse;
    }

    protected function _getStackKey() {
        return 'pages';
    }

    /**
     * @param CM_Page_Abstract $page
     * @param string           $templateName
     * @return string
     */
    private function _fetchTemplate(CM_Page_Abstract $page, $templateName) {
        $viewResponse = new CM_ViewResponse($page);
        $viewResponse->setTemplateName($templateName);
        $viewResponse->setData($page->getParams()->getAll());
        return trim($this->getRender()->renderViewResponse($viewResponse));
    }
}
