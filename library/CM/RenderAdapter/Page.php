<?php

class CM_RenderAdapter_Page extends CM_RenderAdapter_Component {

    public function fetch(CM_Params $viewParams) {
        $this->_getView()->setTplParam('pageTitle', $this->fetchTitle($viewParams));

        return parent::fetch($viewParams);
    }

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
     * @param CM_Params $pageParams
     * @return string
     */
    public function fetchTitle(CM_Params $pageParams) {
        $viewResponse = new CM_ViewResponse($this->_getView());
        $viewResponse->setTemplateName('title');
        $viewResponse->setData($pageParams->getAll());
        return trim($this->getRender()->renderViewResponse($viewResponse));
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
