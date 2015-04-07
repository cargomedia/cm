<?php

class CM_Http_Response_Resource_RootProxy extends CM_Http_Response_Resource_Layout {

    public function __construct(CM_Http_Request_Abstract $request, CM_Service_Manager $serviceManager) {
        $path = $request->getPath();
        $path = preg_replace('#^/resource-#', '', $path);
        $path = '/' . str_replace('--', '/', $path);
        $request->setPath($path);

        parent::__construct($request, $serviceManager);
    }

    public static function match(CM_Http_Request_Abstract $request) {
        return 0 === stripos($request->getPath(), '/resource-');
    }
}
