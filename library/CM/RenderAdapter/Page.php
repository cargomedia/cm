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
     * @return string|null
     */
    public function fetchTitleWithBranding() {
        return $this->_fetchMetaTemplate('title-with-branding', [
            'title' => $this->fetchTitle(),
        ]);
    }

    /**
     * @param string     $templateName
     * @param array|null $variables
     * @return null|string
     */
    protected function _fetchMetaTemplate($templateName, array $variables = null) {
        $templatePath = $this->_getMetaTemplatePath($templateName);
        if (null === $templatePath) {
            return null;
        }
        if (null === $variables) {
            $variables = [];
        }
        $variables = array_merge($this->_getViewResponse()->getData(), $variables);
        $this->_getPage()->checkAccessible($this->getRender()->getEnvironment());
        $html = $this->getRender()->fetchTemplate($templatePath, $variables);
        return trim($html);
    }

    /**
     * @param string $templateName
     * @return string|null
     */
    protected function _getMetaTemplatePath($templateName) {
        $templatePath = $this->getRender()->getTemplatePath($this->_getView(), $templateName);
        if (null === $templatePath) {
            $templatePath = $this->getRender()->getLayoutPath('Page/Abstract/' . $templateName . '.tpl', null, null, null, false);
        }
        return $templatePath;
    }

    /**
     * @return CM_Page_Abstract
     */
    private function _getPage() {
        return $this->_getView();
    }
}
