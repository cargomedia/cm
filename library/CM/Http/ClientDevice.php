<?php

class CM_Http_ClientDevice {

    /** @var \Jenssegers\Agent\Agent */
    protected $_parser;

    /**
     * @param CM_Http_Request_Abstract $request
     */
    public function __construct(CM_Http_Request_Abstract $request) {
        $headerList = array_change_key_case($request->getHeaders(), CASE_UPPER);

        $formattedHeaderList = [];
        foreach ($headerList as $key => $header) {
            if (substr($key, 0, 6) !== 'CONTENT') {
                $key = 'HTTP_' . $key;
            }
            $formattedHeaderList[str_replace('-', '_', $key)] = $header;
        }
        $this->_parser = new Jenssegers\Agent\Agent($formattedHeaderList);
    }

    /**
     * @return bool
     */
    public function isMobile() {
        return $this->_parser->isMobile();
    }
}
