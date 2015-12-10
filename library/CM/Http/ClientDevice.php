<?php

class CM_Http_ClientDevice {

    /** @var \Jenssegers\Agent\Agent */
    protected $_parser;

    /**
     * @param CM_Http_Request_Abstract $request
     */
    public function __construct(CM_Http_Request_Abstract $request) {
        $headerList = [];
        foreach ($request->getHeaders() as $key => $header) {
            if (substr($key, 0, 8) !== 'content-') {
                $headerList['HTTP_' . str_replace('-', '_', strtoupper($key))] = $header;
            }
        }
        $this->_parser = new Jenssegers\Agent\Agent($headerList);
    }

    /**
     * @return bool
     */
    public function isMobile() {
        return $this->_parser->isMobile();
    }
}
