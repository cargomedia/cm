<?php

class CM_RenderAdapter_Page extends CM_RenderAdapter_Abstract {

	public function fetch(array $params = array()) {
		/** @var CM_Page_Abstract $page */
		$page = $this->_getObject();
		$js = $this->getRender()->getJs();

		$options = array();
		$options['renderStamp'] = floor((microtime(true)) * 1000);
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

		$js->onloadReadyJs('cm.component()._ready();');

		$js->registerLanguageValue('%interface.ok');
		$js->registerLanguageValue('%interface.cancel');
		$js->registerLanguageValue('%interface.confirmation_title');

		$this->getLayout()->assign($page->getTplParams());
		$this->getLayout()->assign('page', $page);
		$this->getLayout()->assign('viewer', $page->getViewer());
		$this->getLayout()->assign('js', $js);

		$tplPath = $this->getRender()->getLayoutPath('layout/base.tpl');
		return $this->getLayout()->fetch($tplPath);
	}
}
