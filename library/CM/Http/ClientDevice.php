<?php

class CM_Http_ClientDevice {

    /** @var \Jenssegers\Agent\Agent */
    protected $_parser;

    /**
     * @param CM_Http_Request_Abstract $request
     */
    public function __construct(CM_Http_Request_Abstract $request) {
        $headerList = array_change_key_case($request->getHeaders(), CASE_UPPER);
        $this->_parser = new Jenssegers\Agent\Agent($headerList);
    }

    /**
     * @return bool
     */
    public function isMobile() {
        return $this->_parser->isMobile();
    }
}
