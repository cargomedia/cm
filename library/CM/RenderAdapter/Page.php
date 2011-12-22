<?php

class CM_RenderAdapter_Page extends CM_RenderAdapter_Abstract {

	public function fetch(array $params = array()) {
		/** @var CM_Page_Abstract $page */
		$page = $this->_getObject();
		/** @var CM_RequestHandler_Abstract $requestHandler */
		$requestHandler = $params['requestHandler'];
		$js = $this->getRender()->getJs();

		$page->prepare($requestHandler);

		// Creates header
		$js->onloadHeaderJs("sk.options.renderStamp = " . floor((microtime(true)) * 1000));
		$js->onloadHeaderJs('sk.options.siteId = "' . $this->getRender()->getSite()->getId() . '"');
		$js->onloadHeaderJs('sk.options.urlStatic = "' . URL_STATIC . '"');
		$js->onloadHeaderJs('sk.options.stream = ' . json_encode(Config::get()->stream));
		$js->onloadHeaderJs('WEB_SOCKET_SWF_LOCATION = "' . URL_STATIC . 'swf/WebSocketMainInsecure.swf"');

		if ($viewer = $page->getViewer()) {
			$js->onloadHeaderJs('sk.options.stream.channel = ' . CM_Params::encode(CM_Stream::getStreamChannel($viewer), true));
			$js->onloadHeaderJs('sk.viewer = ' . CM_Params::encode($viewer, true));
		}

		$js->onloadReadyJs('sk.component()._ready();');

		$js->registerLanguageValue('%interface.ok');
		$js->registerLanguageValue('%interface.cancel');
		$js->registerLanguageValue('%interface.confirmation_title');

		$this->getLayout()->assign($page->getTplParams());
		$this->getLayout()->assign('page', $page);
		$this->getLayout()->assign('tracking', CM_Tracking::getInstance()->getHtml());
		$this->getLayout()->assign('viewer', $page->getViewer());
		$this->getLayout()->assign('js', $js);


		$tplPath = $this->getRender()->getLayoutPath('layout/base.tpl');
		return $this->getLayout()->fetch($tplPath);
	}
}
