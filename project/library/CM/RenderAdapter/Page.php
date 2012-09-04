<?php

class CM_RenderAdapter_Page extends CM_RenderAdapter_Abstract {

	public function fetch(array $params = array()) {
		/** @var CM_Page_Abstract $page */
		$page = $this->_getView();
		$page->prepare();

		$js = $this->getRender()->getJs();

		$this->getRender()->pushStack('pages', $page);
		$this->getRender()->pushStack('views', $page);

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

		$this->getRender()->getJs()->registerPage($page);
		$js->onloadReadyJs('cm.findView()._ready();');

		$assign = $page->getTplParams();
		$assign['pageObj'] = $page;
		$html = $this->_renderTemplate('default.tpl', $assign);

		$this->getRender()->popStack('pages');
		$this->getRender()->popStack('views');

		return $html;
	}
}
