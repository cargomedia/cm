<?php

class CM_RenderAdapter_Layout extends CM_RenderAdapter_Abstract {

    /**
     * @param CM_Page_Abstract $page
     * @return string
     */
    public function fetch(CM_Page_Abstract $page) {
        $layout = $this->_getLayout();
        $page->checkAccessible($this->getRender()->getEnvironment());
        $js = $this->getRender()->getJs();



        $renderAdapterPage = new CM_RenderAdapter_Page($this->getRender(), $page);
        $pageTitle = $renderAdapterPage->fetchTitle();

        $viewResponse = new CM_ViewResponse($layout);
        $viewResponse->setTemplateName('default');
        $viewResponse->setData(array(
            'viewObj'         => $layout,
            'title'           => $this->fetchTitle($pageTitle),
            'page'            => $page,
            'pageDescription' => $renderAdapterPage->fetchDescription(),
            'pageKeywords'    => $renderAdapterPage->fetchKeywords(),
        ));
        $this->getRender()->getJs()->registerViewResponse($viewResponse);

        $this->getRender()->pushStack('layouts', $viewResponse);
        $this->getRender()->pushStack('views', $viewResponse);

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
        $js->getOnloadHeaderJs()->append('cm.options = ' . CM_Params::encode($options, true));

        if ($viewer = $this->getRender()->getViewer()) {
            $js->getOnloadHeaderJs()->append('cm.viewer = ' . CM_Params::encode($viewer, true));
        }

        $js->getOnloadHeaderJs()->append('cm.ready();');

        $html = $this->getRender()->fetchViewResponse($viewResponse);

        $js->getOnloadReadyJs()->append('cm.getLayout()._ready();');

        $this->getRender()->popStack('layouts');
        $this->getRender()->popStack('views');

        return $html;
    }

    /**
     * @param string $pageTitle
     * @return string
     */
    public function fetchTitle($pageTitle) {
        return $this->_fetchTemplate('title', array('pageTitle' => $pageTitle));
    }

    /**
     * @return CM_Layout_Abstract
     */
    private function _getLayout() {
        return $this->_getView();
    }
}
