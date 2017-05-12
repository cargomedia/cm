<?php

use CM\Url\ServiceWorkerUrl;

class CM_Http_Response_Resource_Javascript_ServiceWorker extends CM_Http_Response_Resource_Javascript_Abstract {

    const PATH_PREFIX_FILENAME = 'serviceworker';

    protected function _process() {
        $debug = $this->getEnvironment()->isDebug();

        $this->_setAsset(new CM_Asset_Javascript_ServiceWorker($this->getSite(), $debug));
    }

    public static function createFromRequest(CM_Http_Request_Abstract $request, CM_Site_Abstract $site, CM_Service_Manager $serviceManager) {
        if ($request->getUrl() instanceof ServiceWorkerUrl) {
            $request = clone $request;
            return new self($request, $site, $serviceManager);
        }
        return null;
    }

}
