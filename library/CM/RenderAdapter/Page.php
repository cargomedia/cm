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

    /**
     * @param string $templateName
     * @return string
     */
    protected function _fetchMetaTemplate($templateName) {
        $templatePath = $this->_getMetaTemplatePath($templateName);
        return trim($this->getRender()->fetchTemplate($templatePath, $this->_getViewResponse()->getData(), true));
    }

    /**
     * @param string $templateName
     * @throws CM_Exception_Invalid
     * @return string
     */
    protected function _getMetaTemplatePath($templateName) {
        $templatePath = $this->getRender()->getTemplatePath($this->_getView(), $templateName);
        if (null === $templatePath) {
            $templatePath = $this->getRender()->getLayoutPath('Page/Abstract/' . $templateName, null, null, false);
        }
        if (null === $templatePath) {
            throw new CM_Exception_Invalid('Cannot find page-meta template `' . $templateName . '` for `' . get_class($this->_getView()) . '`');
        }
        return $templatePath;
    }
}
