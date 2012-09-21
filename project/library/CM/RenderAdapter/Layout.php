<?php

class CM_RenderAdapter_Layout extends CM_RenderAdapter_Abstract {

	public function fetch(array $params = array()) {
		/** @var CM_Layout_Abstract $layout */
		$layout = $this->_getView();

		$js = $this->getRender()->getJs();

		$this->getRender()->pushStack('layouts', $layout);
		$this->getRender()->pushStack('views', $layout);
		$this->getRender()->pushStack('pages', $layout->getPage());

		$options = array();
		$options['releaseStamp'] = CM_App::getInstance()->getReleaseStamp();
		$options['renderStamp'] = floor(microtime(true) * 1000);
		$options['siteId'] = $this->getRender()->getSite()->getId();
		$options['url'] = $this->getRender()->getUrl();
		$options['urlStatic'] = $this->getRender()->getUrlStatic();
		$options['urlUserContent'] = $this->getRender()->getUrlUserContent();
		$options['urlResource'] = $this->getRender()->getUrlResource();
		$options['language'] = $this->getRender()->getLanguage();
		$options['debug'] = $this->getRender()->isDebug();
		$options['stream'] = array();
		$options['stream']['enabled'] = CM_Stream::getEnabled();
		if (CM_Stream::getEnabled()) {
			$options['stream']['adapter'] = CM_Stream::getAdapterClass();
			$options['stream']['server'] = CM_Stream::getServer();
		}
		if ($viewer = $this->getRender()->getViewer()) {
			$options['stream']['channel'] = CM_Stream::getStreamChannel($viewer);
		}
		$js->onloadHeaderJs('cm.options = ' . CM_Params::encode($options, true));

		$js->onloadHeaderJs('WEB_SOCKET_SWF_LOCATION = "' . $this->getRender()->getUrlStatic('/swf/WebSocketMainInsecure.swf') . '"');
		if ($viewer = $this->getRender()->getViewer()) {
			$js->onloadHeaderJs('cm.viewer = ' . CM_Params::encode($viewer, true));
		}

		$js->onloadHeaderJs('cm.ready();');

		$this->getRender()->getJs()->registerLayout($layout);
		$js->onloadReadyJs('cm.findView()._ready();');
		$js->onloadReadyJs('cm.router.ready();');

		$renderAdapterPage = new CM_RenderAdapter_Page($this->getRender(), $layout->getPage());
		$layout->setTplParam('pageTitle', $renderAdapterPage->fetchTitle());
		$layout->setTplParam('pageDescription', $renderAdapterPage->fetchDescription());
		$layout->setTplParam('pageKeywords', $renderAdapterPage->fetchKeywords());

		$assign = $layout->getTplParams();
		$assign['viewObj'] = $layout;
		$html = $this->_renderTemplate('default.tpl', $assign);

		$this->getRender()->popStack('layouts');
		$this->getRender()->popStack('views');
		$this->getRender()->popStack('pages');

		return $html;
	}
}
