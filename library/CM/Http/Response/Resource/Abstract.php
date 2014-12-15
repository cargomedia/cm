<?php

abstract class CM_Http_Response_Resource_Abstract extends CM_Http_Response_Abstract {

    public function __construct(CM_Http_Request_Abstract $request) {
        parent::__construct($request);
        $timestamp = $this->_request->popPathPart();
    }

    protected function _setContent($content) {
        $this->setHeader('Access-Control-Allow-Origin', $this->getSite()->getUrl());
        parent::_setContent($content);
    }
}
