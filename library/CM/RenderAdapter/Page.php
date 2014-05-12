<?php

class CM_RenderAdapter_Page extends CM_RenderAdapter_Component {

    protected function _prepareViewResponse(CM_Frontend_ViewResponse $viewResponse) {
        $viewResponse->set('pageTitle', $this->fetchTitle());
    }

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

    protected function _getStackKey() {
        return 'pages';
    }

    /**
     * @param string $templateName
     * @return string
     */
    protected function _fetchMetaTemplate($templateName) {
        return trim(parent::_fetchTemplate($templateName, $this->_getViewResponse()->getData()));
    }
}
