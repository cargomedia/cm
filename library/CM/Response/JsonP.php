<?php

class CM_Response_JsonP extends CM_Response_Abstract {

    public function __construct(CM_Request_Abstract $request) {
        $request->popPathPart(0);
        $request->popPathLanguage();
        $this->_request = $request;
        $this->_site = CM_Site_Abstract::findByRequest($request);
    }

    protected function _process() {
        $data = $this->_loadPage();
        $content =
            '<script type="text/javascript">parent.cm.findView(\'CM_Layout_Abstract\').injectPage(' . CM_Params::encode($data, true) . ');</script>';
        $this->_setContent($content);
    }

    protected function _loadPage() {
        $request = new CM_Request_Get(CM_Util::link($this->getRequest()->getPath(), $this->getRequest()->getQuery()), $this->getRequest()->getHeaders(), $this->getRequest()->getServer(), $this->getRequest()->getViewer());

        $count = 0;
        $paths = array($request->getPath());
        do {
            $url = $this->getRender()->getUrl(CM_Util::link($request->getPath(), $request->getQuery()));
            if ($count++ > 10) {
                throw new CM_Exception_Invalid('Page redirect loop detected (' . implode(' -> ', $paths) . ').');
            }
            $responsePage = new CM_Response_Page_Embed($request);
            $responsePage->process();
            $paths[] = $request->getPath();

            if ($redirectUrl = $responsePage->getRedirectUrl()) {
                $redirectExternal = (0 !== mb_stripos($redirectUrl, $this->getRender()->getUrl()));
                if ($redirectExternal) {
                    return array('redirectExternal' => $redirectUrl);
                }
            }
        } while ($redirectUrl);

        foreach ($responsePage->getCookies() as $name => $cookieParameters) {
            $this->setCookie($name, $cookieParameters['value'], $cookieParameters['expire'], $cookieParameters['path']);
        }

        $page = $responsePage->getPage();

        $this->_setStringRepresentation(get_class($page));

        $html = $responsePage->getContent();
        $js = $responsePage->getRender()->getJs()->getJs();
        $responsePage->getRender()->getJs()->clear();

        $title = $responsePage->getTitle();
        $layoutClass = get_class($page->getLayout($this->getSite()));
        $menuList = array_merge($this->getSite()->getMenus(), $responsePage->getRender()->getMenuList());
        $menuEntryHashList = $this->_getMenuEntryHashList($menuList, $page);

        return array('autoId'            => $page->getAutoId(), 'html' => $html, 'js' => $js, 'title' => $title, 'url' => $url,
                     'layoutClass'       => $layoutClass,
                     'menuEntryHashList' => $menuEntryHashList);
    }

    /**
     * @param CM_Menu[]        $menuList
     * @param CM_Page_Abstract $page
     * @return string[]
     */
    private function _getMenuEntryHashList(array $menuList, CM_Page_Abstract $page) {
        $menuEntryHashList = array();
        foreach ($menuList as $menu) {
            if (is_array($menuEntries = $menu->findEntries($page))) {
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

    public static function match(CM_Request_Abstract $request) {
        return $request->getPathPart(0) === 'jsonp';
    }
}
