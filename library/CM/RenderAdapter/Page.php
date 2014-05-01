<?php

class CM_RenderAdapter_Page extends CM_RenderAdapter_Component {

    /**
     * @return string
     */
    public function fetchDescription() {
        return $this->_fetchMetaTemplate('meta-description');
    }

    /**
     * @return string
     */
    public function fetchKeywords() {
        return $this->_fetchMetaTemplate('meta-keywords');
    }

    /**
     * @return string
     */
    public function fetchTitle() {
        return $this->_fetchMetaTemplate('title');
    }

    protected function _getPreparedViewResponse(CM_RenderEnvironment $environment, CM_ViewFrontendHandler $frontendHandler) {
        $viewResponse = parent::_getPreparedViewResponse($environment, $frontendHandler);
        $viewResponse->set('pageTitle', $this->fetchTitle());
        return $viewResponse;
    }

    protected function _getStackKey() {
        return 'pages';
    }

    /**
     * @param string $templateName
     * @return string
     */
    protected function _fetchMetaTemplate($templateName) {
        return trim(parent::_fetchTemplate($templateName, $this->_getView()->getParams()->getAll()));
    }
}
