<?php

class CM_Http_Response_Page_Embed extends CM_Http_Response_Page {

    /**
     * @param CM_Http_Request_Abstract $request
     * @param CM_Site_Abstract         $site
     * @param CM_Service_Manager       $serviceManager
     */
    public function __construct(CM_Http_Request_Abstract $request, CM_Site_Abstract $site, CM_Service_Manager $serviceManager) {
        $this->_request = clone $request;
        $this->_request->popPathLanguage();
        $this->_site = $site;

        $this->setServiceManager($serviceManager);
    }

    /** @var string|null */
    private $_title;

    /**
     * @throws CM_Exception_Invalid
     * @return string
     */
    public function getTitle() {
        if (null === $this->_title) {
            throw new CM_Exception_Invalid('Unprocessed page has no title');
        }
        return $this->_title;
    }

    /**
     * @param CM_Page_Abstract $page
     * @return string
     */
    protected function _renderPage(CM_Page_Abstract $page) {
        $renderAdapterLayout = new CM_RenderAdapter_Layout($this->getRender(), $page);
        $this->_title = $renderAdapterLayout->fetchTitle();
        return $renderAdapterLayout->fetchPage();
    }

    protected function _process() {
        $this->_processContentOrRedirect();
    }
}
