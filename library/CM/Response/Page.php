<?php

class CM_Response_Page extends CM_Response_Abstract {

    /** @var CM_Page_Abstract|null */
    private $_page;

    /** @var CM_Params|null */
    private $_pageParams;

    /** @var string|null */
    private $_redirectUrl;

    public function __construct(CM_Request_Abstract $request) {
        $this->_request = $request;
        $this->_site = CM_Site_Abstract::findByRequest($this->_request);
        $this->_request->popPathLanguage();
    }

    /**
     * @throws CM_Exception_Invalid
     * @return CM_Page_Abstract
     */
    public function getPage() {
        if (null === $this->_page) {
            throw new CM_Exception_Invalid('Page not set');
        }
        return $this->_page;
    }

    /**
     * @throws CM_Exception_Invalid
     * @return CM_Params
     */
    public function getPageParams() {
        if (null == $this->_pageParams) {
            throw new CM_Exception_Invalid('Page params not set');
        }
        return $this->_pageParams;
    }

    /**
     * @return string|null
     */
    public function getRedirectUrl() {
        return $this->_redirectUrl;
    }

    /**
     * @param string $url
     */
    public function setRedirectHeader($url) {
        $this->setHeader('Location', (string) $url);
    }

    /**
     * @param CM_Page_Abstract|string $page
     * @param array|null              $params
     */
    public function redirect($page, array $params = null) {
        $url = $this->getRender()->getUrlPage($page, $params);
        $this->redirectUrl($url);
    }

    /**
     * @param string $url
     */
    public function redirectUrl($url) {
        $this->_redirectUrl = (string) $url;
    }

    /**
     * @param CM_Request_Abstract $request
     * @throws CM_Exception_Invalid
     * @return string|null
     */
    protected function _processPageLoop(CM_Request_Abstract $request) {
        $count = 0;
        $paths = array($request->getPath());
        while (false === ($html = $this->_processPage($request))) {
            $paths[] = $request->getPath();
            if ($count++ > 10) {
                throw new CM_Exception_Invalid('Page dispatch loop detected (' . implode(' -> ', $paths) . ').');
            }
        }
        return $html;
    }

    /**
     * @param CM_Page_Abstract $page
     * @return string
     */
    protected function _renderPage(CM_Page_Abstract $page) {
        $renderAdapterLayout = new CM_RenderAdapter_Layout($this->getRender(), $page);
        return $renderAdapterLayout->fetch();
    }

    protected function _process() {
        $this->_site->preprocessPageResponse($this);
        if ($this->_site->getHost() !== $this->_request->getHost()) {
            $path = CM_Util::link($this->_request->getPath(), $this->_request->getQuery());
            $this->redirectUrl($this->getRender()->getUrl($path, $this->_site));
        }
        if (!$this->getRedirectUrl()) {
            $this->getRender()->getServiceManager()->getTrackings()->trackPageView($this->getRender()->getEnvironment());
            $html = $this->_processPageLoop($this->getRequest());
            $this->_setContent($html);
        }
        if ($redirectUrl = $this->getRedirectUrl()) {
            $this->setRedirectHeader($redirectUrl);
        }
    }

    /**
     * @param CM_Request_Abstract $request
     * @throws CM_Exception_Nonexistent
     * @throws CM_Exception
     * @throws CM_Exception_Nonexistent
     * @return string|null|boolean
     */
    private function _processPage(CM_Request_Abstract $request) {
        try {
            $this->getSite()->rewrite($request);
            $pageParams = CM_Params::factory($request->getQuery());

            try {
                $className = CM_Page_Abstract::getClassnameByPath($this->getRender(), $request->getPath());
                $page = CM_Page_Abstract::factory($className, $pageParams);
            } catch (CM_Exception $ex) {
                throw new CM_Exception_Nonexistent('Cannot load page `' . $request->getPath() . '`: ' . $ex->getMessage());
            }

            $this->_setStringRepresentation(get_class($page));
            if ($this->getViewer() && $request->getLanguageUrl()) {
                $this->redirect($page);
            }
            $page->prepareResponse($this->getRender()->getEnvironment(), $this);
            if ($this->getRedirectUrl()) {
                $request->setUri($this->getRedirectUrl());
                return null;
            }
            $html = $this->_renderPage($page);
            $this->_page = $page;
            $this->_pageParams = $pageParams;
            return $html;
        } catch (CM_Exception $e) {
            if (!array_key_exists(get_class($e), $this->_getConfig()->catch)) {
                throw $e;
            }
            $this->getRender()->getGlobalResponse()->clear();
            $path = $this->_getConfig()->catch[get_class($e)];
            $request->setPath($path);
            $request->setQuery(array());
        }
        return false;
    }
}
