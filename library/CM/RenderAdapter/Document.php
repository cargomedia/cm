<?php

class CM_RenderAdapter_Document extends CM_RenderAdapter_Abstract {

    /** @var CM_Page_Abstract */
    private $_page;

    /**
     * @param CM_Frontend_Render $render
     * @param CM_Page_Abstract   $page
     */
    public function __construct(CM_Frontend_Render $render, CM_Page_Abstract $page) {
        $this->_page = $page;

        $documentClass = $render->getSite()->getDocument();
        $document = new $documentClass();

        parent::__construct($render, $document);
    }

    /**
     * @return string
     */
    public function fetch() {
        $render = $this->getRender();

        $page = $this->_getPage();
        $layoutClass = $page->getLayout($render->getEnvironment());
        $layout = new $layoutClass(['page' => $page]);
        $document = $this->_getDocument();

        $renderAdapterPage = new CM_RenderAdapter_Page($render, $page);
        $renderAdapterLayout = new CM_RenderAdapter_Layout($render, $layout);

        $page->checkAccessible($render->getEnvironment());

        $viewResponse = new CM_Frontend_ViewResponse($document);
        $frontend = $render->getGlobalResponse();
        $frontend->treeExpand($viewResponse);

        $viewResponse->setTemplateName('default');
        $document->prepare($render->getEnvironment(), $viewResponse);
        $viewResponse->set('viewResponse', $viewResponse);
        $viewResponse->set('page', $page);
        $viewResponse->set('layoutContent', $renderAdapterLayout->fetch());
        $viewResponse->set('title', $renderAdapterPage->fetchTitleWithBranding());
        $viewResponse->set('metaDescription', $renderAdapterPage->fetchDescription());
        $viewResponse->set('metaKeywords', $renderAdapterPage->fetchKeywords());
        $webFontLoaderConfig = $render->getSite()->getWebFontLoaderConfig();
        if ($webFontLoaderConfig) {
            $viewResponse->set('webFontLoaderConfig', CM_Params::encode($webFontLoaderConfig, true));
        }

        $environmentDefault = new CM_Frontend_Environment($render->getEnvironment()->getSite());
        $renderDefault = new CM_Frontend_Render($environmentDefault);
        $viewResponse->set('renderDefault', $renderDefault);
        $viewResponse->set('languageList', new CM_Paging_Language_Enabled());

        $frontend->getOnloadHeaderJs()->append('cm.options = ' . CM_Params::encode($this->_getOptions(), true));
        if ($viewer = $render->getViewer()) {
            $frontend->getOnloadHeaderJs()->append('cm.viewer = ' . CM_Params::encode($viewer, true));
        }

        $frontend->getOnloadReadyJs()->append('cm.getLayout()._ready();');
        $frontend->getOnloadHeaderJs()->append('cm.ready();');
        $html = $render->fetchViewResponse($viewResponse);

        $frontend->treeCollapse();
        return $html;
    }

    /**
     * @return CM_Page_Abstract
     */
    private function _getPage() {
        return $this->_page;
    }

    /**
     * @return CM_View_Document
     */
    private function _getDocument() {
        return $this->_getView();
    }

    /**
     * @return array
     */
    private function _getOptions() {
        $serviceManager = CM_Service_Manager::getInstance();
        $site = $this->getRender()->getSite();

        $options = array();
        $options['name'] = CM_App::getInstance()->getName();
        $options['deployVersion'] = CM_App::getInstance()->getDeployVersion();
        $options['renderStamp'] = floor(microtime(true) * 1000);
        $options['site'] = CM_Params::encode($site);
        $options['url'] = $site->getUrl()->getUriBaseComponents();
        $options['urlBase'] = $site->getUrl()->withoutPrefix()->getUriBaseComponents();
        $options['urlCdn'] = $site->getUrlCdn()->getUriBaseComponents();
        $options['urlUserContentList'] = $serviceManager->getUserContent()->getUrlList();
        $options['urlServiceWorker'] = $this->getRender()->getUrlServiceWorker();
        $options['language'] = $this->getRender()->getLanguage();

        $options['debug'] = CM_Bootloader::getInstance()->isDebug();
        $options['stream'] = $serviceManager->getStreamMessage()->getClientOptions();
        if ($viewer = $this->getRender()->getViewer()) {
            $options['stream']['channel']['key'] = CM_Model_StreamChannel_Message_User::getKeyByUser($viewer);
            $options['stream']['channel']['type'] = CM_Model_StreamChannel_Message_User::getTypeStatic();
        }
        return $options;
    }
}
