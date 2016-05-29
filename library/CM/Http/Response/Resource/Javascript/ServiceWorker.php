<?php

class CM_Http_Response_Resource_Javascript_ServiceWorker extends CM_Http_Response_Resource_Javascript_Abstract {

    public function __construct(CM_Http_Request_Abstract $request, CM_Service_Manager $serviceManager) {
        $path = $request->getPath();
        $path = str_replace('-', '/', $path);
        $request->setPath($path);

        parent::__construct($request, $serviceManager);
    }

    protected function _process() {
        $debug = $this->getEnvironment()->isDebug();

        $this->_setAsset(new CM_Asset_Javascript_ServiceWorker($this->getSite(), $debug));
    }

    public static function match(CM_Http_Request_Abstract $request) {
        return 0 === stripos($request->getPath(), '/serviceworker-');
    }
}
