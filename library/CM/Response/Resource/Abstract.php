<?php

abstract class CM_Response_Resource_Abstract extends CM_Response_Abstract {

	public function __construct(CM_Request_Abstract $request) {
		parent::__construct($request);
		$timestamp = $request->popPathPart();
	}

	protected function _setContent($content) {
		$this->setHeader('Access-Control-Allow-Origin', $this->getSite()->getUrl());
		parent::_setContent($content);
	}
}
