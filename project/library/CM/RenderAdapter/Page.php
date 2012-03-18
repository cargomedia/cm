<?php

class CM_RenderAdapter_Page extends CM_RenderAdapter_Abstract {

	public function fetch(array $params = array()) {
		/** @var CM_Page_Abstract $page */
		$page = $this->_getView();
		$js = $this->getRender()->getJs();

		$this->getRender()->pushStack('pages', $page);

		$options = array();
		$options['renderStamp'] = floor(microtime(true) * 1000);
		$options['siteId'] = $this->getRender()->getSite()->getId();
		$options['urlStatic'] = URL_STATIC;
		$options['stream'] = array();
		$options['stream']['enabled'] = CM_Stream::getEnabled();
		if (CM_Stream::getEnabled()) {
			$options['stream']['adapter'] = CM_Stream::getAdapterClass();
			$options['stream']['server'] = CM_Stream::getServer();
		}
		if ($viewer = $page->getViewer()) {
			$options['stream']['channel'] = CM_Stream::getStreamChannel($viewer);
		}
		$js->onloadHeaderJs('cm.options = ' . CM_Params::encode($options, true));

		$js->onloadHeaderJs('WEB_SOCKET_SWF_LOCATION = "' . URL_STATIC . 'swf/WebSocketMainInsecure.swf"');
		if ($viewer = $page->getViewer()) {
			$js->onloadHeaderJs('cm.viewer = ' . CM_Params::encode($viewer, true));
		}

		$this->getRender()->getJs()->registerPage($page);
		$js->onloadReadyJs('cm.findView()._ready();');

		$js->registerLanguageValue('%interface.ok');
		$js->registerLanguageValue('%interface.cancel');
		$js->registerLanguageValue('%interface.confirmation_title');

		$tplPath = $this->_getTplPath('default.tpl');

		$this->getTemplate()->assign($page->getTplParams());
		$this->getTemplate()->assign('page', $page);
		$this->getTemplate()->assign('viewer', $page->getViewer());

		$html = $this->getTemplate()->fetch($tplPath);

		$this->getRender()->popStack('pages');

		return $html;
	}
}
