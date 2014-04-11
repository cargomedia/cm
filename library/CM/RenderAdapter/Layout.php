<?php

class CM_RenderAdapter_Layout extends CM_RenderAdapter_Abstract {

    /**
     * @param CM_Layout_Abstract $layout
     * @param CM_Page_Abstract   $page
     * @return string
     */
    public function fetch(CM_Layout_Abstract $layout, CM_Page_Abstract $page) {
        $page->checkAccessible($this->getRender()->getEnvironment());
        $js = $this->getRender()->getJs();

        $this->getRender()->pushStack('layouts', $layout);
        $this->getRender()->pushStack('views', $layout);
        $this->getRender()->pushStack('pages', $page);

        $options = array();
        $options['deployVersion'] = CM_App::getInstance()->getDeployVersion();
        $options['renderStamp'] = floor(microtime(true) * 1000);
        $options['site'] = CM_Params::encode($this->getRender()->getSite());
        $options['url'] = $this->getRender()->getUrl();
        $options['urlStatic'] = $this->getRender()->getUrlStatic();
        $options['urlUserContent'] = $this->getRender()->getUrlUserContent();
        $options['urlResource'] = $this->getRender()->getUrlResource();
        $options['language'] = $this->getRender()->getLanguage();
        $options['debug'] = CM_Bootloader::getInstance()->isDebug();
        $options['stream'] = array();
        $options['stream']['enabled'] = CM_Stream_Message::getInstance()->getEnabled();
        if (CM_Stream_Message::getInstance()->getEnabled()) {
            $options['stream']['adapter'] = CM_Stream_Message::getInstance()->getAdapterClass();
            $options['stream']['options'] = CM_Stream_Message::getInstance()->getOptions();
        }
        if ($viewer = $this->getRender()->getViewer()) {
            $options['stream']['channel']['key'] = CM_Model_StreamChannel_Message_User::getKeyByUser($viewer);
            $options['stream']['channel']['type'] = CM_Model_StreamChannel_Message_User::getTypeStatic();
        }
        $js->onloadHeaderJs('cm.options = ' . CM_Params::encode($options, true));

        if ($viewer = $this->getRender()->getViewer()) {
            $js->onloadHeaderJs('cm.viewer = ' . CM_Params::encode($viewer, true));
        }

        $js->onloadHeaderJs('cm.ready();');

        $this->getRender()->getJs()->registerLayout($layout);
        $js->onloadReadyJs('cm.getLayout()._ready();');

        $renderAdapterPage = new CM_RenderAdapter_Page($this->getRender(), $page);
        $pageTitle = $renderAdapterPage->fetchTitle();

        $viewResponse = new CM_ViewResponse($this->_getView());
        $viewResponse->setTemplateName('default');
        $viewResponse->setData(array(
            'viewObj'         => $this->_getView(),
            'title'           => $this->fetchTitle($pageTitle),
            'page'            => $page,
            'pageDescription' => $renderAdapterPage->fetchDescription($page),
            'pageKeywords'    => $renderAdapterPage->fetchKeywords($page),
        ));
        $html = $this->getRender()->renderViewResponse($viewResponse);

        $this->getRender()->popStack('layouts');
        $this->getRender()->popStack('views');
        $this->getRender()->popStack('pages');

        return $html;
    }

    /**
     * @param string $pageTitle
     * @return string
     */
    public function fetchTitle($pageTitle) {
        $viewResponse = new CM_ViewResponse($this->_getView());
        $viewResponse->setTemplateName('title');
        $viewResponse->addData('pageTitle', $pageTitle);
        return trim($this->getRender()->renderViewResponse($viewResponse));
    }
}
