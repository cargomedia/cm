<?php

class CM_Http_Response_Resource_Layout_Root extends CM_Http_Response_Resource_Layout {

    public function __construct(CM_Http_Request_Abstract $request, CM_Service_Manager $serviceManager) {
        $this->_request = clone $request;
        $this->_site = CM_Site_Abstract::findByRequest($this->_request);

        $this->setServiceManager($serviceManager);
    }

    protected function _process() {
        $path = $this->getRequest()->getPath();
        $path = self::unmarshalPath($path);
        $this->_processPath($path);
    }

    public static function match(CM_Http_Request_Abstract $request) {
        return 0 === stripos($request->getPath(), '/resource-layout-');
    }

    /**
     * @param string $path
     * @return string
     * @throws CM_Exception_Invalid
     */
    public static function marshalPath($path) {
        if (false !== stripos($path, '--')) {
            throw new CM_Exception_Invalid('Cannot marshal path which contains `--`.');
        }
        $path = str_replace('/', '--', $path);
        $path = '/resource-layout-' . $path;
        return $path;
    }

    /**
     * @param string $path
     * @return string
     */
    public static function unmarshalPath($path) {
        $path = preg_replace('#^/resource-layout-#', '', $path);
        $path = str_replace('--', '/', $path);
        return $path;
    }
}
