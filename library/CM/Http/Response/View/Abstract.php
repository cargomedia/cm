<?php

use CM\Url\Url;
use League\Uri\Components\Query;

abstract class CM_Http_Response_View_Abstract extends CM_Http_Response_Abstract {

    /**
     * @param array $output
     * @return array
     */
    abstract protected function _processView(array $output);

    /**
     * @param array|null $additionalParams
     */
    public function reloadComponent(array $additionalParams = null) {
        $componentInfo = $this->_getViewInfo('CM_Component_Abstract');
        $componentId = $componentInfo['id'];
        $componentClassName = $componentInfo['className'];
        $componentParams = CM_Params::factory($componentInfo['params']);

        if ($additionalParams) {
            foreach ($additionalParams as $key => $value) {
                $componentParams->set($key, $value);
            }
        }
        $component = CM_Component_Abstract::factory($componentClassName, $componentParams);
        $renderAdapter = CM_RenderAdapter_Component::factory($this->getRender(), $component);
        $html = $renderAdapter->fetch();

        $frontend = $this->getRender()->getGlobalResponse();
        $autoId = $frontend->getTreeRoot()->getValue()->getAutoId();

        $componentReferenceOld = 'cm.views["' . $componentId . '"]';
        $componentReferenceNew = 'cm.views["' . $autoId . '"]';
        $frontend->getOnloadHeaderJs()->append('cm.window.appendHidden(' . json_encode($html) . ');');
        $frontend->getOnloadPrepareJs()->append($componentReferenceOld . '.getParent().registerChild(' . $componentReferenceNew . ');');
        $frontend->getOnloadPrepareJs()->append($componentReferenceOld . '.replaceWithHtml(' . $componentReferenceNew . '.$el);');
        $frontend->getOnloadReadyJs()->append('cm.views["' . $autoId . '"]._ready();');
    }

    /**
     * @param string    $className
     * @param CM_Params $params
     * @return array
     */
    public function loadComponent($className, CM_Params $params) {
        $component = CM_Component_Abstract::factory($className, $params);
        return $this->_getComponentRendering($component);
    }

    /**
     * @param CM_Params                  $params
     * @param CM_Http_Response_View_Ajax $response
     * @throws CM_Exception_Invalid
     * @return array
     */
    public function loadPage(CM_Params $params, CM_Http_Response_View_Ajax $response) {
        $path = $params->getString('path');
        $currentLayoutClass = $params->getString('currentLayout');

        $request = $this->_createGetRequestWithUrl($path);
        $responseFactory = new CM_Http_ResponseFactory($this->getServiceManager());

        $count = 0;
        $fragments = [];
        $baseUrl = $this->getRender()->getSite()->getUrl()->withoutPrefix();
        do {
            $fragment = (string) Url::create($request->getPath())->withQuery((string) Query::createFromPairs($request->getQuery()));
            $fragments[] = $fragment;
            $url = (string) Url::create($fragment)->withBaseUrl($baseUrl);
            if ($count++ > 10) {
                throw new CM_Exception_Invalid('Page redirect loop detected (' . implode(' -> ', $fragments) . ').');
            }

            $responsePage = $responseFactory->getResponse($request);
            if (!$responsePage->getSite()->equals($this->getSite())) {
                return array('redirectExternal' => (string) $responsePage->getUrl());
            }

            $responseEmbed = new CM_Http_Response_Page_Embed($responsePage->getRequest(), $responsePage->getSite(), $this->getServiceManager());
            $responseEmbed->process();
            $request = $responseEmbed->getRequest();

            if ($redirectUrl = $responseEmbed->getRedirectUrl()) {
                if (!$this->_isPageOnSameSite($redirectUrl)) {
                    return array('redirectExternal' => $redirectUrl);
                }
            }
        } while ($redirectUrl);

        foreach ($responseEmbed->getCookies() as $name => $cookieParameters) {
            $response->setCookie($name, $cookieParameters['value'], $cookieParameters['expire'], $cookieParameters['path']);
        }
        $page = $responseEmbed->getPage();

        $this->_setStringRepresentation(get_class($page));

        $frontend = $responseEmbed->getRender()->getGlobalResponse();
        $html = $responseEmbed->getContent();
        $js = $frontend->getJs();
        $autoId = $frontend->getTreeRoot()->getValue()->getAutoId();

        $frontend->clear();

        $layoutRendering = null;
        $layoutClass = $page->getLayout($this->getRender()->getEnvironment());
        if ($layoutClass !== $currentLayoutClass) {
            $layout = new $layoutClass();
            $layoutRendering = $this->_getComponentRendering($layout);
        }

        $title = $responseEmbed->getTitle();
        $menuList = array_merge($this->getSite()->getMenus($this->getRender()->getEnvironment()), $responseEmbed->getRender()->getMenuList());
        $menuEntryHashList = $this->_getMenuEntryHashList($menuList, get_class($page), $page->getParams());
        $jsTracking = $responseEmbed->getRender()->getServiceManager()->getTrackings()->getJs();

        return [
            'pageRendering'     => [
                'js'     => $js,
                'html'   => $html,
                'autoId' => $autoId,
            ],
            'layoutRendering'   => $layoutRendering,
            'title'             => $title,
            'url'               => $url,
            'menuEntryHashList' => $menuEntryHashList,
            'jsTracking'        => $jsTracking,
        ];
    }

    /**
     * @param CM_Component_Abstract $component
     * @return array
     */
    protected function _getComponentRendering(CM_Component_Abstract $component) {
        $render = $this->createRender();
        $renderAdapter = CM_RenderAdapter_Component::factory($render, $component);
        $html = $renderAdapter->fetch();

        $globalResponse = $render->getGlobalResponse();
        return [
            'js'     => $globalResponse->getJs(),
            'html'   => $html,
            'autoId' => $globalResponse->getTreeRoot()->getValue()->getAutoId(),
        ];
    }

    public function popinComponent() {
        $componentInfo = $this->_getViewInfo('CM_Component_Abstract');
        $this->getRender()->getGlobalResponse()->getOnloadJs()->append('cm.views["' . $componentInfo['id'] . '"].popIn();');
    }

    /**
     * Add a reload to the response.
     */
    public function reloadPage() {
        $this->getRender()->getGlobalResponse()->getOnloadJs()->append('window.location.reload(true)');
    }

    /**
     * @param CM_Page_Abstract|string $page
     * @param array|null              $params
     * @param boolean|null            $forceReload
     *
     */
    public function redirect($page, array $params = null, $forceReload = null) {
        $forceReload = (boolean) $forceReload;
        $url = $this->getRender()->getUrlPage($page, $params);
        $this->redirectUrl($url, $forceReload);
    }

    /**
     * @param string       $url
     * @param boolean|null $forceReload
     */
    public function redirectUrl($url, $forceReload = null) {
        $url = (string) $url;
        $forceReload = (boolean) $forceReload;
        $js = 'cm.router.route(' . json_encode($url) . ', ' . json_encode($forceReload) . ');';
        $this->getRender()->getGlobalResponse()->getOnloadPrepareJs()->append($js);
    }

    protected function _process() {
        $output = array();
        $this->_runWithCatching(function () use (&$output) {
            $output = $this->_processView($output);
        }, function (CM_Exception $e, array $errorOptions) use (&$output) {
            $output['error'] = $e->getClientData($this->getRender());
        });
        $output['deployVersion'] = CM_App::getInstance()->getDeployVersion();

        $this->setHeader('Content-Type', 'application/json');
        $this->_setContent(json_encode($output));
    }

    /**
     * @param string|null $className
     * @return CM_View_Abstract
     */
    protected function _getView($className = null) {
        if (null === $className) {
            $className = 'CM_View_Abstract';
        }
        $viewInfo = $this->_getViewInfo($className);
        /** @var CM_View_Abstract $className */
        return $className::factory($viewInfo['className'], CM_Params::factory($viewInfo['params'], true));
    }

    /**
     * @param string|null $className
     * @return array
     * @throws CM_Exception_Invalid
     */
    protected function _getViewInfo($className = null) {
        if (null === $className) {
            $className = 'CM_View_Abstract';
        }
        $query = $this->_request->getQuery();
        if (!array_key_exists('viewInfoList', $query)) {
            throw new CM_Exception_Invalid('viewInfoList param not found.', CM_Exception::WARN);
        }
        $viewInfoList = $query['viewInfoList'];
        if (!array_key_exists($className, $viewInfoList)) {
            throw new CM_Exception_Invalid('View not set.', CM_Exception::WARN, ['viewClassName' => $className]);
        }
        $viewInfo = $viewInfoList[$className];
        if (!is_array($viewInfo)) {
            throw new CM_Exception_Invalid('View is not an array', CM_Exception::WARN, ['viewClassName' => $className]);
        }
        if (!isset($viewInfo['id'])) {
            throw new CM_Exception_Invalid('View id not set.', CM_Exception::WARN, ['viewClassName' => $className]);
        }
        if (!isset($viewInfo['className']) || !class_exists($viewInfo['className']) || !is_a($viewInfo['className'], 'CM_View_Abstract', true)) {
            throw new CM_Exception_Invalid('View className  is illegal.', CM_Exception::WARN, [
                'viewClassName'     => $className,
                'viewInfoClassName' => $viewInfo['className'],
            ]);
        }
        if (!isset($viewInfo['params'])) {
            throw new CM_Exception_Invalid('View params not set.', CM_Exception::WARN, ['viewClassName' => $className]);
        }
        if (!isset($viewInfo['parentId'])) {
            $viewInfo['parentId'] = null;
        }
        return array(
            'id'        => (string) $viewInfo['id'],
            'className' => (string) $viewInfo['className'],
            'params'    => (array) $viewInfo['params'],
            'parentId'  => (string) $viewInfo['parentId']
        );
    }

    /**
     * @param CM_Menu[] $menuList
     * @param string    $pageName
     * @param CM_Params $pageParams
     * @return string[]
     */
    private function _getMenuEntryHashList(array $menuList, $pageName, CM_Params $pageParams) {
        $pageName = (string) $pageName;
        $menuEntryHashList = array();
        foreach ($menuList as $menu) {
            if (is_array($menuEntries = $menu->findEntries($pageName, $pageParams))) {
                foreach ($menuEntries as $menuEntry) {
                    $menuEntryHashList[] = $menuEntry->getHash();
                    foreach ($menuEntry->getParents() as $parentEntry) {
                        $menuEntryHashList[] = $parentEntry->getHash();
                    }
                }
            }
        }
        return $menuEntryHashList;
    }

    /**
     * @param string $url
     * @return CM_Http_Request_Get
     */
    private function _createGetRequestWithUrl($url) {
        $request = $this->getRequest();
        return new CM_Http_Request_Get($url, $request->getHeaders(), $request->getServer(), $request->getViewer());
    }

    /**
     * @param string $url
     * @return bool
     */
    private function _isPageOnSameSite($url) {
        $url = Url::createFromString($url);
        if (!$this->_site->isUrlMatch($url->getHost(), $url->getPath())) {
            return false;
        }

        $request = $this->_createGetRequestWithUrl((string) $url);
        $responseFactory = new CM_Http_ResponseFactory($this->getServiceManager());
        $response = $responseFactory->getResponse($request);
        return $response->getSite()->equals($this->getSite());
    }

}
