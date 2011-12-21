<?php

class CM_RenderAdapter_Page extends CM_RenderAdapter_Abstract {

	public function fetch(array $params = array()) {
		/** @var CM_Page_Abstract $page */
		$page = $this->_getObject();
		$js = $this->getRender()->getJs();
		
		// Creates header
		$js->onloadHeaderJs("sk.options.renderStamp = " . floor((microtime(true)) * 1000));
		$js->onloadHeaderJs('sk.options.siteId = "' . $this->getRender()->getSite()->getId() . '"');
		$js->onloadHeaderJs('sk.options.urlStatic = "' . URL_STATIC . '"');
		$js->onloadHeaderJs('sk.options.stream = ' . json_encode(Config::get()->stream));
		$js->onloadHeaderJs('WEB_SOCKET_SWF_LOCATION = "' . URL_STATIC . 'swf/WebSocketMainInsecure.swf"');
		
		if ($viewer = $page->getViewer()) {
			$js->onloadHeaderJs('sk.options.stream.channel = ' . json_encode(CM_Stream::getStreamChannel($viewer)));
			$js->onloadHeaderJs('sk.viewer = ' . CM_Params::encode($viewer->getProfile(), true));
		}

		$js->onloadJs('sk.component()._ready();');

		$js->registerLanguageValue('%interface.ok');
		$js->registerLanguageValue('%interface.cancel');
		$js->registerLanguageValue('%interface.confirmation_title');

		$this->getLayout()->assign('tracking', CM_Tracking::getInstance()->getHtml());
		$this->getLayout()->assign('page', $page);
		$this->getLayout()->assign('js', $js);
		
		// Renders header and body
		return $this->getLayout()->fetch($this->getRender()->getLayoutPath('layout/base.tpl'));		
	}
}
