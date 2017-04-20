<?php

class CM_Http_Response_Page_Embed extends CM_Http_Response_Page {

    /** @var bool|null */
    protected $_forceReload;

    /** @var CM_Layout_Abstract|null */
    protected $_layoutContext;

    /** @var string|null */
    protected $_title;

    /**
     * @param CM_Http_Request_Abstract $request
     * @param CM_Site_Abstract         $site
     * @param CM_Service_Manager       $serviceManager
     * @param CM_Layout_Abstract|null  $layoutContext
     */
    public function __construct(CM_Http_Request_Abstract $request, CM_Site_Abstract $site, CM_Service_Manager $serviceManager, CM_Layout_Abstract $layoutContext = null) {
        parent::__construct($request, $site, $serviceManager);
        $this->_layoutContext = $layoutContext;
    }

    /**
     * @return bool
     */
    public function getForceReload() {
        return (bool) $this->_forceReload;
    }

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

    protected function _forceReload() {
        $this->_forceReload = true;
    }

    /**
     * @param CM_Page_Abstract $page
     * @return string
     */
    protected function _renderPage(CM_Page_Abstract $page) {
        $renderAdapterPage = new CM_RenderAdapter_Page($this->getRender(), $page);
        $this->_title = $renderAdapterPage->fetchTitleWithBranding();
        return $renderAdapterPage->fetch();
    }

    public static function createFromRequest(CM_Http_Request_Abstract $request, CM_Site_Abstract $site, CM_Service_Manager $serviceManager) {
        return null;
    }

}
