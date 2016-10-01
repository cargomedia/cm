<?php

class CM_RenderAdapter_Layout extends CM_RenderAdapter_Component {

    /** @var CM_Page_Abstract */
    private $_page;

    /**
     * @param CM_Frontend_Render $render
     * @param CM_Page_Abstract   $page
     */
    public function __construct(CM_Frontend_Render $render, CM_Page_Abstract $page) {
        $this->_page = $page;

        $environment = $render->getEnvironment();
        $layoutClass = $page->getLayout($environment);
        $layout = new $layoutClass(['page' => $page]);

        parent::__construct($render, $layout);
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

}
