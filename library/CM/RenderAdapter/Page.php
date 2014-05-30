<?php

class CM_RenderAdapter_Page extends CM_RenderAdapter_Component {

    protected function _prepareViewResponse(CM_Frontend_ViewResponse $viewResponse) {
        $viewResponse->set('pageTitle', $this->fetchTitle());
    }

    /**
     * @return string|null
     */
    public function fetchDescription() {
        return $this->_fetchMetaTemplate('meta-description');
    }

    /**
     * @return string|null
     */
    public function fetchKeywords() {
        return $this->_fetchMetaTemplate('meta-keywords');
    }

    /**
     * @return string|null
     */
    public function fetchTitle() {
        return $this->_fetchMetaTemplate('title');
    }

    /**
     * @param string $templateName
     * @return string|null
     */
    protected function _fetchMetaTemplate($templateName) {
        $templatePath = $this->_getMetaTemplatePath($templateName);
        if (null === $templatePath) {
            return null;
        }
        return trim($this->getRender()->fetchTemplate($templatePath, $this->_getViewResponse()->getData(), true));
    }

    /**
     * @param string $templateName
     * @return string|null
     */
    protected function _getMetaTemplatePath($templateName) {
        $templatePath = $this->getRender()->getTemplatePath($this->_getView(), $templateName);
        if (null === $templatePath) {
            $templatePath = $this->getRender()->getLayoutPath('Page/Abstract/' . $templateName . '.tpl', null, null, false);
        }
        return $templatePath;
    }
}
