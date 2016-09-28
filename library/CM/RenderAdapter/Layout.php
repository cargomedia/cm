<?php

class CM_RenderAdapter_Layout extends CM_RenderAdapter_Component {

    /** @var CM_Page_Abstract */
    private $_page;

    /** @var CM_RenderAdapter_Page */
    private $_renderAdapterPage;

    /**
     * @param CM_Frontend_Render $render
     * @param CM_Page_Abstract   $page
     */
    public function __construct(CM_Frontend_Render $render, CM_Page_Abstract $page) {
        $this->_page = $page;
        $this->_renderAdapterPage = new CM_RenderAdapter_Page($render, $page);

        $environment = $render->getEnvironment();
        $layout = $page->getLayout($environment);

        parent::__construct($render, $layout);
    }

    /**
     * @return string
     */
    public function fetchTitle() {
        $pageTitle = $this->_renderAdapterPage->fetchTitle();
        return $this->getRender()->fetchViewTemplate($this->_getLayout(), 'title', array('pageTitle' => $pageTitle));
    }

    /**
     * @return string
     */
    public function fetchDescription() {
        return $this->_renderAdapterPage->fetchDescription();
    }

    /**
     * @return string
     */
    public function fetchKeywords() {
        return $this->_renderAdapterPage->fetchKeywords();
    }

    protected function _prepareViewResponse(CM_Frontend_ViewResponse $viewResponse) {
        $viewResponse->set('page', $this->_getPage());
    }

    /**
     * @return CM_Page_Abstract
     */
    private function _getPage() {
        return $this->_page;
    }

    /**
     * @return CM_Layout_Abstract
     */
    private function _getLayout() {
        return $this->_getView();
    }
}
