<?php

use CM\Url\Url;

class CM_Http_Response_Page extends CM_Http_Response_Abstract {

    /** @var CM_Page_Abstract|null */
    private $_page;

    /** @var string|null */
    private $_redirectUrl;

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
     * @param CM_Page_Abstract $page
     * @return string
     */
    protected function _renderPage(CM_Page_Abstract $page) {
        $renderAdapterDocument = new CM_RenderAdapter_Document($this->getRender(), $page);
        return $renderAdapterDocument->fetch();
    }

    protected function _process() {
        $this->setHeaderDisableCache();
        $this->_site->preprocessPageResponse($this);
        $this->_processContentOrRedirect();
        if ($redirectUrl = $this->getRedirectUrl()) {
            $this->setRedirectHeader($redirectUrl);
        }
    }

    protected function _processContentOrRedirect() {
        if ($this->getSite()->getUrl()->getHost() !== $this->getRequest()->getHost()) {
            $this->redirectUrl((string) $this->getUrl()->withSite($this->getSite()));
        }
        if (!$this->getRedirectUrl()) {
            $html = $this->_processPageLoop($this->getRequest());
            $this->_setContent($html);
        }
    }

    /**
     * @param CM_Http_Request_Abstract $request
     * @throws CM_Exception_Invalid
     * @return string|null
     */
    protected function _processPageLoop(CM_Http_Request_Abstract $request) {
        $processingResult = new CM_Http_Response_Page_ProcessingResult();
        $count = 0;
        while (false === $this->_processPage($request, $processingResult)) {
            if ($count++ > 10) {
                throw new CM_Exception_Invalid('Page dispatch loop detected.', null, [
                    'processingRequestList' => implode(' -> ', $processingResult->getPathList()),
                ]);
            }
        }

        $this->_setStringRepresentation(get_class($processingResult->getPageInitial()));
        $this->_page = $processingResult->getPage();
        return $processingResult->hasHtml() ? $processingResult->getHtml() : null;
    }

    /**
     * @param CM_Http_Request_Abstract               $request
     * @param CM_Http_Response_Page_ProcessingResult $result
     * @throws CM_Exception
     * @throws Exception
     * @return boolean
     */
    private function _processPage(CM_Http_Request_Abstract $request, CM_Http_Response_Page_ProcessingResult $result) {
        return $this->_runWithCatching(function () use ($request, $result) {
            $url = Url::createWithParams($request->getPath(), $request->getQuery());
            $result->addPath($url->getUriRelativeComponents());

            $this->getSite()->rewrite($request);
            $pageParams = CM_Params::factory($request->getQuery(), true);

            try {
                $className = CM_Page_Abstract::getClassnameByPath($this->getRender(), $request->getPath());
                $page = CM_Page_Abstract::factory($className, $pageParams);
            } catch (CM_Exception $ex) {
                throw new CM_Exception_Nonexistent('Cannot load page', null, [
                    'requestPath'              => $request->getPath(),
                    'originalExceptionMessage' => $ex->getMessage(),
                ]);
            }
            $result->addPage($page);

            $environment = $this->getRender()->getEnvironment();
            $page->prepareResponse($environment, $this);
            if ($this->getRedirectUrl()) {
                $request->setUri($this->getRedirectUrl());
                return true;
            }
            if ($page->getCanTrackPageView()) {
                $this->getRender()->getServiceManager()->getTrackings()->trackPageView($environment, $result->getPathTracking());
            }
            $result->setHtml($this->_renderPage($page));
            return true;
        }, function (CM_Exception $ex, array $errorOptions) use ($request) {
            $this->getRender()->getGlobalResponse()->clear();
            /** @var CM_Page_Abstract $errorPage */
            $errorPage = $errorOptions['errorPage'];
            $request->setPath($errorPage::getPath());
            $request->setQuery(array());
            return false;
        });
    }

    public static function createFromRequest(CM_Http_Request_Abstract $request, CM_Site_Abstract $site, CM_Service_Manager $serviceManager) {
        $request = clone $request;
        $request->popPathLanguage();
        return new self($request, $site, $serviceManager);
    }

    public static function catchAll() {
        return true;
    }

}
