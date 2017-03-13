<?php

class CM_Http_Response_Resource_Javascript_ServiceWorker extends CM_Http_Response_Resource_Javascript_Abstract {

    protected function _process() {
        $this->_setAsset(new CM_Asset_Javascript_Bundle_ServiceWorker($this->getSite()));
    }

    public static function createFromRequest(CM_Http_Request_Abstract $request, CM_Site_Abstract $site, CM_Service_Manager $serviceManager) {
        if ($request->hasPathPrefix('/serviceworker-')) {
            $request = clone $request;
            $request->setPath(str_replace('-', '/', $request->getPath()));
            $request->popPathPart(0);
            $request->popPathLanguage();
            $deployVersion = $request->popPathPart(0);
            return new self($request, $site, $serviceManager);
        }
        return null;
    }

}
